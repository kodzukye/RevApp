# actions.py
from rasa_sdk import Action, Tracker
from rasa_sdk.events import SlotSet
from rasa_sdk.executor import CollectingDispatcher
from typing import Text, List, Dict, Any
import fitz  # PyMuPDF
import google.generativeai as genai
from sentence_transformers import SentenceTransformer, util
import os
from dotenv import load_dotenv
load_dotenv()  # Charge les variables du fichier .env


# Configuration Gemini
genai.configure(api_key=os.getenv("GEMINI_API_KEY"))  # À mettre dans les variables d'environnement
MODEL_NAME = 'gemini-1.5-flash'
SEMANTIC_MODEL = "dangvantuan/sentence-camembert-base"

class ActionCapturePdfPath(Action):
    def name(self) -> Text:
        return "action_capture_pdf_path"

    def run(self, dispatcher, tracker, domain):
        message = tracker.latest_message.get("text")
        return [SlotSet("pdf_path", message)]

class ActionAnalysePDF(Action):
    def __init__(self):
        self.model = genai.GenerativeModel(MODEL_NAME)
        self.semantic_model = SentenceTransformer(SEMANTIC_MODEL)

    def name(self) -> Text:
        return "action_analyser_cours"

    def _extraire_contenu_pdf(self, chemin: Text) -> Text:
        """Extraction PDF optimisée avec PyMuPDF"""
        try:
            doc = fitz.open(chemin)
            contenu = []
            for page in doc:
                text_dict = page.get_text("dict", flags=fitz.TEXT_PRESERVE_LIGATURES)
                for block in text_dict["blocks"]:
                    if "lines" in block:
                        for line in block["lines"]:
                            line_text = "".join([span["text"] for span in line["spans"]])
                            contenu.append(line_text)
            return '\n'.join(contenu)
        except Exception as e:
            raise ValueError(f"Erreur lecture PDF : {str(e)}")

    def _generer_question(self, contexte: Text) -> Text:
        """Génération de question via Gemini"""
        try:
            prompt = f"Génère une question technique en français basée sur : {contexte[:2000]}"
            response = self.model.generate_content(prompt)
            return response.text.strip()
        except Exception as e:
            raise RuntimeError(f"Erreur Gemini : {str(e)}")

    def _generer_reponse_ref(self, question: Text) -> Text:
        """Génération de la réponse de référence"""
        try:
            prompt = f"Donne une réponse concise à : {question}"
            response = self.model.generate_content(prompt)
            return response.text.strip()
        except Exception as e:
            return "Réponse non disponible"

    def _evaluer_reponse(self, question: Text, reponse: Text, reference: Text) -> bool:
        """Évaluation sémantique des réponses"""
        embeddings = self.semantic_model.encode([question, reponse, reference])
        similarite = util.cos_sim(embeddings[1], embeddings[2]).item()
        return similarite > 0.65

    async def run(self, dispatcher, tracker, domain):
        pdf_path = tracker.get_slot("pdf_path")
        
        if not pdf_path:
            dispatcher.utter_message("Veuillez fournir un chemin PDF valide.")
            return []

        try:
            contexte = self._extraire_contenu_pdf(pdf_path)
            questions = [self._generer_question(contexte) for _ in range(5)]
            
            # Stocker les questions dans le slot
            tracker.slots["questions"] = questions
            tracker.slots["current_question"] = 0
            
            # Poser la première question
            dispatcher.utter_message(f"Question 1: {questions[0]}")
            return [SlotSet("questions", questions), SlotSet("current_question", 0)]

        except Exception as e:
            dispatcher.utter_message(f"Erreur : {str(e)}")
            return []
# Ajouter en haut du fichier
# Correction nécessaire
class PDFUtils:
    def __init__(self):
        self.semantic_model = SentenceTransformer(SEMANTIC_MODEL)
    
    def evaluer_reponse(self, question: Text, reponse: Text, reference: Text) -> bool:
        embeddings = self.semantic_model.encode([question, reponse, reference])
        similarite = util.cos_sim(embeddings[1], embeddings[2]).item()
        return similarite > 0.65



class ActionGererReponse(Action):
    def __init__(self):
        self.utils = PDFUtils()  # Initialisation correcte
        self.model = genai.GenerativeModel(MODEL_NAME)

    def name(self) -> Text:
        return "action_gerer_reponse"

    async def run(self, dispatcher: CollectingDispatcher, tracker: Tracker, domain: Dict[Text, Any]) -> List[Dict[Text, Any]]:
        current_idx = tracker.get_slot("current_question") or 0
        questions = tracker.get_slot("questions")
        
        if not questions or current_idx >= len(questions):
            dispatcher.utter_message("Aucune question en cours.")
            return []

        reponse = tracker.latest_message.get("text")
        question = questions[current_idx]

        try:
            # Génération réponse de référence
            reponse_ref = self.model.generate_content(
                f"Réponse courte à : {question}"
            ).text.strip()

            # Évaluation
            if self.utils.evaluer_reponse(question, reponse, reponse_ref):
                dispatcher.utter_message("✅ Correct !")
            else:
                dispatcher.utter_message(f"❌ Réponse attendue : {reponse_ref}")

            # Passage à la question suivante
            if current_idx < len(questions) - 1:
                new_idx = current_idx + 1
                dispatcher.utter_message(f"Question {new_idx + 1}: {questions[new_idx]}")
                return [SlotSet("current_question", new_idx)]
            
            return [SlotSet("current_question", None), SlotSet("questions", None)]

        except Exception as e:
            dispatcher.utter_message(f"Erreur d'évaluation : {str(e)}")
            return []

