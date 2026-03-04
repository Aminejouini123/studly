import json
import sys
import os
from analyzer import MotivationAnalyzer
from scheduler import Scheduler
from pdf_generator import generate_schedule_pdf

def main():
    """
    Main entry point for the Time Management service.
    Expects a JSON string as input from stdin.
    """
    try:
        # 1. Load Input
        if len(sys.argv) > 1:
            # Assume first arg is JSON string (for testing)
            data = json.loads(sys.argv[1])
        else:
            # Wait for data from stdin
            data = json.load(sys.stdin)
            
        user_state = data.get('user_state', {})
        tasks = data.get('tasks', [])
        
        # 2. Analyze Motivation
        analyzer = MotivationAnalyzer()
        motivation_data = analyzer.calculate_motivation(
            energy=user_state.get('energy', 5),
            stress=user_state.get('stress', 5),
            sleep_quality=user_state.get('sleep_quality', 5),
            mood_text=user_state.get('mood_text', "")
        )
        
        # 3. Optimize Schedule
        scheduler = Scheduler(motivation_level=motivation_data['level'])
        optimized_schedule = scheduler.optimize_tasks(tasks)
        
        # 4. Generate PDF
        output_dir = os.path.join(os.getcwd(), 'exports')
        if not os.path.exists(output_dir):
            os.makedirs(output_dir)
            
        timestamp = user_state.get('date', 'today').replace('/', '-')
        pdf_path = os.path.join(output_dir, f"planning_{timestamp}.pdf")
        
        generate_schedule_pdf(motivation_data, optimized_schedule, pdf_path)
        
        # 5. Output Result JSON
        result = {
            "status": "success",
            "motivation": motivation_data,
            "pdf_path": pdf_path,
            "optimized_tasks": optimized_schedule
        }
        # 5. Output Result JSON - Forced ASCII for safe transport across pipe on Windows
        print(json.dumps(result, indent=4, ensure_ascii=True))
        
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
