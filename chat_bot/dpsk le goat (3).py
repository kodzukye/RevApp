import os
import re
import fitz  # PyMuPDF
import google.generativeai as genai
from sentence_transformers import SentenceTransformer, util

# Configuration
MAX_QUESTIONS = 5
SEMANTIC_MODEL = "dangvantuan/sentence-camembert-base"
PDF_PATH = "Gestion_BDD.pdf"

# Configuration Gemini
genai.configure(api_key="AIzaSyDcqdB26ktrmfONFq3z7m5uMED5r3M4xKw")  # Remplacez par votre clé Gemini
model = genai.GenerativeModel('gemini-1.5-flash')

def extraire_contenu_pdf(chemin_pdf):
    """Extraction structurée du contenu PDF"""
    contenu = []
    try:
        doc = fitz.open(chemin_pdf)
        for page in doc:
            text_dict = page.get_text("dict", flags=fitz.TEXT_PRESERVE_LIGATURES)
            for block in text_dict["blocks"]:
                if "lines" in block:
                    for line in block["lines"]:
                        line_text = []
                        for span in line["spans"]:
                            line_text.append(span["text"])
                        contenu.append("".join(line_text))
        return '\n'.join(contenu)
    except Exception as e:
        print(f"Erreur d'extraction : {str(e)}")
        return ""

def generer_question_gemini(contexte):
    """Génération d'une question via l'API Gemini"""
    try:
        prompt = f"""
        Génère une question technique précise en français basée sur ce contexte :
        {contexte[:2000]}
        """
        response = model.generate_content(prompt)
        return response.text.strip()
    except Exception as e:
        print(f"Erreur de génération : {str(e)}")
        return ""

def evaluer_reponse(question, reponse_utilisateur, reponse_attendue):
    """Évaluation sémantique de la réponse"""
    semantic_model = SentenceTransformer(SEMANTIC_MODEL)
    embeddings = semantic_model.encode([question, reponse_utilisateur, reponse_attendue])
    similarite = util.cos_sim(embeddings[1], embeddings[2]).item()
    return similarite > 0.65

if __name__ == "__main__":
    # Extraction du contenu
    contexte = extraire_contenu_pdf(PDF_PATH)
    
    if not contexte:
        print("Aucun contenu extrait - vérifiez le fichier PDF")
        exit()

    # Génération interactive
    for i in range(1, MAX_QUESTIONS + 1):
        # Génération de la question
        question = generer_question_gemini(contexte)
        if not question:
            continue
            
        print(f"\nQUESTION {i}: {question}")
        reponse_utilisateur = input("Votre réponse : ").strip()
        
        # Génération réponse de référence
        reponse_ref = model.generate_content(
            f"Donne la réponse courte à la question suivante : {question}"
        ).text.strip()
        
        # Évaluation
        if evaluer_reponse(question, reponse_utilisateur, reponse_ref):
            print("✅ Réponse correcte !")
        else:
            print(f"❌ Meilleure réponse : {reponse_ref}")
