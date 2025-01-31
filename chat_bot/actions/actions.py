# This files contains your custom actions which can be used to run
# custom Python code.
#
# See this guide on how to implement these action:
# https://rasa.com/docs/rasa/custom-actions


# This is a simple example for a custom action which utters "Hello World!"

from rasa_sdk import Action
from rasa_sdk.events import SlotSet
import fitz
from transformers import AutoModelForSeq2SeqLM, AutoTokenizer
import random


class ActionCapturePdfPath(Action):
    def name(self) -> str:
        return "action_capture_pdf_path"
    
    def run(self, dispatcher, tracker, domain):
        # Récupérer le dernier message de l'utilisateur
        message = tracker.latest_message.get("text")

        #sauvegarder le chemin dans le slot pdf_path
        return [SlotSet("pdf_path", message)]

class PoserQuestion(Action):
    def __init__(self):
        self.model_name = "lincoln/barthez-squadFR-fquad-piaf-question-generation"
        self.model = AutoModelForSeq2SeqLM.from_pretrained(self.model_name)
        self.tokenizer = AutoTokenizer.from_pretrained(self.model_name)
        self.prompts_variés = [
            "À partir du texte suivant, pose une question approfondie : ",
            "Quelle question pertinente peut-on déduire du contenu suivant : ",
            "Formule une question détaillée basée sur ce passage : ",
            "Génère une question complexe à propos de : ",
            "Quelle question pourrait-on poser concernant ce texte : ",
            "À partir de ce texte, formule une question détaillée : ",
            "Voici un passage, propose une question pertinente : "
        ]

    def name(self) -> str:
        return "action_poser_question"

    def diviser_texte_en_sections(self, texte, longueur_max=300):
        sections = []
        paragraphes = texte.split("\n\n")
        section_actuelle = ""
        for paragraphe in paragraphes:
            if len(section_actuelle) + len(paragraphe) <= longueur_max:
                section_actuelle += paragraphe + "\n\n"
            else:
                sections.append(section_actuelle.strip())
                section_actuelle = paragraphe + "\n\n"
        if section_actuelle:
            sections.append(section_actuelle.strip())
        return sections

    def generate_question_with_context(self, text):
        prompt = random.choice(self.prompts_variés)
        input_text = f"{prompt}{text}"
        input_ids = self.tokenizer.encode(input_text, return_tensors="pt", max_length=512, truncation=True)
        outputs = self.model.generate(
            input_ids,
            max_length=64,
            num_return_sequences=1,
            do_sample=True,
            top_k=50,
            top_p=0.95,
            temperature=1.3
        )
        return self.tokenizer.decode(outputs[0], skip_special_tokens=True)

    def generate_qcm_from_sections(self, sections, num_questions_per_section=2):
        qcm = []
        for section in sections:
            for _ in range(num_questions_per_section):
                question = self.generate_question_with_context(section)
                if "?" in question:
                    qcm.append(question)
        return self.remove_duplicate_questions(qcm)

    def remove_duplicate_questions(self, qcm):
        longueur_qcm = len(qcm) - 1
        # Parcours la liste qcm question par question via leurs index
        for index_prcp_question in range(longueur_qcm):
            # Arrête la principale boucle si l'index de la question est supérieur ou égale à la longueur de la liste
            if index_prcp_question >= longueur_qcm:
                break


            for index_scde_question in range(0, longueur_qcm + 1):

                # Arrête la boucle si les index de la question ou de la seconde question sont supérieur à la longueur de la liste 
                if (index_scde_question > longueur_qcm) or (index_prcp_question > longueur_qcm):
                    break

                # Ignore si les index de la question principale et secondaire sont égaux
                if (index_prcp_question == index_scde_question):
                    continue

                # Efface la question secondaire de la liste qcm si elle est égale à la question principale et la soustrait à la longeur de la liste
                if qcm[index_prcp_question] == qcm[index_scde_question]:
                    qcm.remove(qcm[index_scde_question])
                    longueur_qcm -= 1
                    continue

                # Arrête la boucle secondaire si l'index de la question secondaire est supérieur ou égale à la longeur de la liste 
                if (index_scde_question >= longueur_qcm):
                    break

                # Efface la question secondaire de la liste qcm si elle est égal à la question qui la succède et la soustrait à la longeur de la liste
                if qcm[index_scde_question + 1] == qcm[index_scde_question]:
                    qcm.remove(qcm[index_scde_question])
                    longueur_qcm -= 1
                    continue

                # Supprimer la question principal si elle ne contient pas d'espace avant un " ?"
                if qcm[index_prcp_question][-2] != ' ':
                    qcm.remove(qcm[index_prcp_question])
                    longueur_qcm -= 1 
                    continue
        return qcm

    def run(self, dispatcher, tracker, domain):
        try:
            chemin_pdf = tracker.get_slot("pdf_path")
            if not chemin_pdf:
                dispatcher.utter_message("Veuillez d'abord me donner le chemin du fichier PDF.")
                return []

            document = fitz.open(chemin_pdf)
            texte_complet = ""
            for numero_page, page in enumerate(document, start=1):
                texte = page.get_text()
                texte_complet += f"\n--- Page {numero_page} ---\n{texte}"
            document.close()

            sections = self.diviser_texte_en_sections(texte_complet)
            
            questions = self.generate_qcm_from_sections(sections, 5)
            
            dispatcher.utter_message("Voici les questions générées :")
            for i, question in enumerate(questions, 1):
                dispatcher.utter_message(f"Question {i}: {question}")

            return [SlotSet("pdf_content", texte_complet)]
        
        except Exception as e:
            dispatcher.utter_message(f"Erreur lors de la génération des questions : {e}")
            return []