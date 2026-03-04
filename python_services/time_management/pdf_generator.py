from fpdf import FPDF
from datetime import datetime

class PDFGenerator(FPDF):
    def header(self):
        self.set_font('Arial', 'B', 15)
        self.cell(0, 10, 'Planning de Gestion du Temps - Étudiant', 0, 1, 'C')
        self.ln(5)

    def footer(self):
        self.set_y(-15)
        self.set_font('Arial', 'I', 8)
        self.cell(0, 10, f'Généré le {datetime.now().strftime("%d/%m/%Y %H:%M")} | Page ' + str(self.page_no()), 0, 0, 'C')

def generate_schedule_pdf(motivation_data, optimized_schedule, output_path):
    pdf = PDFGenerator()
    pdf.add_page()
    
    # Section: Analyse de Motivation
    pdf.set_font('Arial', 'B', 12)
    pdf.set_text_color(255, 255, 255)
    pdf.set_fill_color(52, 73, 94)
    pdf.cell(0, 10, ' 1. Analyse de votre État Initial', 0, 1, 'L', True)
    pdf.ln(2)
    
    pdf.set_font('Arial', '', 10)
    pdf.set_text_color(0, 0, 0)
    pdf.cell(50, 8, f'Niveau de Motivation:', 0, 0)
    
    # Color code for level
    level = motivation_data['level']
    if level == "Élevé":
        pdf.set_text_color(39, 174, 96) # Green
    elif level == "Moyen":
        pdf.set_text_color(243, 156, 18) # Orange
    else:
        pdf.set_text_color(192, 57, 43) # Red
        
    pdf.set_font('Arial', 'B', 10)
    pdf.cell(0, 8, level, 0, 1)
    
    pdf.set_font('Arial', '', 10)
    pdf.set_text_color(0, 0, 0)
    pdf.cell(50, 8, f'Score Global:', 0, 0)
    pdf.cell(0, 8, f"{motivation_data['score']:.1f}/100", 0, 1)
    pdf.ln(5)
    
    # Section: Planning Optimisé
    pdf.set_font('Arial', 'B', 12)
    pdf.set_text_color(255, 255, 255)
    pdf.set_fill_color(41, 128, 185)
    pdf.cell(0, 10, ' 2. Votre Planning de Travail Optimisé', 0, 1, 'L', True)
    pdf.ln(2)
    
    # Table Header
    pdf.set_font('Arial', 'B', 10)
    pdf.set_text_color(0, 0, 0)
    pdf.set_fill_color(236, 240, 241)
    pdf.cell(80, 8, 'Tâche', 1, 0, 'C', True)
    pdf.cell(30, 8, 'Difficulté', 1, 0, 'C', True)
    pdf.cell(40, 8, 'Structure (Pomodoro)', 1, 0, 'C', True)
    pdf.cell(40, 8, 'Temps Total', 1, 1, 'C', True)
    
    pdf.set_font('Arial', '', 9)
    for task in optimized_schedule:
        pdf.cell(80, 8, task['title'], 1, 0)
        pdf.cell(30, 8, f"Niveau {task['original_difficulty']}", 1, 0, 'C')
        pdf.cell(40, 8, f"{task['blocks']} x {task['block_duration']}m", 1, 0, 'C')
        pdf.cell(40, 8, f"{task['total_time']} min", 1, 1, 'C')
        
    pdf.ln(10)
    
    # Section: Conseils
    pdf.set_font('Arial', 'B', 11)
    pdf.cell(0, 8, 'Conseils Personnalisés:', 0, 1)
    pdf.set_font('Arial', 'I', 10)
    if level == "Faible":
        pdf.multi_cell(0, 6, "Votre motivation est basse. Commencez par les tâches les plus faciles pour obtenir des 'petites victoires' et n'hésitez pas à faire des pauses plus longues.")
    elif level == "Élevé":
        pdf.multi_cell(0, 6, "Profitez de votre grande énergie pour attaquer les sujets les plus complexes dès maintenant. Restez concentré sur vos blocs de travail longs.")
    else:
        pdf.multi_cell(0, 6, "Un bon équilibre aujourd'hui. Gardez une approche structurée avec des pauses régulières pour maintenir ce rythme.")
        
    pdf.output(output_path)
    return output_path
