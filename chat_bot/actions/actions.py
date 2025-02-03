# actions.py corrigé et optimisé

from rasa_sdk import Action, Tracker
from rasa_sdk.events import SlotSet
from rasa_sdk.executor import CollectingDispatcher
from typing import Any, Text, Dict, List
import fitz  # PyMuPDF
import google.generativeai as genai
from sentence_transformers import SentenceTransformer, util
import os
from dotenv import load_dotenv

load_dotenv()

# Configuration Gemini
genai.configure(api_key=os.getenv("GEMINI_API_KEY"))
MODEL_NAME = 'gemini-1.5-flash'
SEMANTIC_MODEL = "dangvantuan/sentence-camembert-base"
MAX_QUESTIONS = 5  # Nombre maximal de questions

class PDFAnalyzer:
    """Classe utilitaire partagée pour l'analyse PDF"""
    
    def __init__(self):
        self.model = genai.GenerativeModel(MODEL_NAME)
        self.semantic_model = SentenceTransformer(SEMANTIC_MODEL)
        self.questions = []
        self.current_question_idx = 0

    def extraire_contenu_pdf(self, chemin_pdf: str) -> str:
        """Extrait le contenu textuel d'un PDF"""
        try:
            doc = fitz.open(chemin_pdf)
            return '\n'.join(page.get_text() for page in doc)
        except Exception as e:
            raise RuntimeError(f"Erreur PDF : {str(e)}")

    def generer_questions(self, contexte: str) -> List[str]:
        """Génère les questions via Gemini"""
        try:
            prompt = f"""
            Génère {MAX_QUESTIONS} questions techniques en français basées sur ce contexte :
            {contexte[:2000]}
            Format de sortie : Liste numérotée
            """
            response = self.model.generate_content(prompt)
            return [q.split('. ', 1)[1] for q in response.text.split('\n') if q]
        except Exception as e:
            raise RuntimeError(f"Erreur Gemini : {str(e)}")

    def evaluer_reponse(self, question: str, reponse: str) -> str:
        """Évalue la réponse utilisateur"""
        try:
            # Génération réponse de référence
            reponse_ref = self.model.generate_content(
                f"Donne la réponse courte à : {question}"
            ).text.strip()
            
            # Comparaison sémantique
            embeddings = self.semantic_model.encode([reponse, reponse_ref])
            similarite = util.cos_sim(embeddings[0], embeddings[1]).item()
            
            return f"✅ Correct !" if similarite > 0.65 else f"❌ Réponse attendue : {reponse_ref}"
        except Exception as e:
            return f"Erreur d'évaluation : {str(e)}"

class ActionDemarrerRevision(Action, PDFAnalyzer):
    def name(self) -> Text:
        return "action_analyser_cours"

    async def run(self, dispatcher, tracker, domain):
        try:
            pdf_path = tracker.get_slot("pdf_path")
            contexte = self.extraire_contenu_pdf(pdf_path)
            questions = self.generer_questions(contexte)

            if not questions:
                raise ValueError("Aucune question générée")

            # Envoi de toutes les questions d'un coup
            message = "Voici les questions générées :\n\n"
            for i, question in enumerate(questions, 1):
                message += f"Question {i}: {question}\n\n"

            dispatcher.utter_message(text=message)
            return []

        except Exception as e:
            dispatcher.utter_message(text=f"Échec de l'analyse : {str(e)}")
            return []


#class ActionGererReponse(Action):
#    def name(self) -> Text:
#        return "action_gerer_reponse"

#    def run(self, dispatcher: CollectingDispatcher, tracker: Tracker, domain: Dict[Text, Any]) -> List[Dict[Text, Any]]:
#        question_courante = tracker.get_slot("question_courante")
#        reponse_utilisateur = tracker.latest_message.get("text")
        
#        if question_courante:
#            evaluation = self.evaluer_reponse(question_courante, reponse_utilisateur)
#            dispatcher.utter_message(text=evaluation)
#            return [SlotSet("question_courante", None)]
#        else:
#            dispatcher.utter_message(text="Désolé, je n'ai pas de question en attente de réponse.")
#        return []
