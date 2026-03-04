import cv2
import time
import json
import requests
import sys
import threading
import numpy as np
import os
import sys

project_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))

# Redirect stdout/stderr strictly to a file to avoid crashes and capture errors
sys.stdout = open(os.devnull, 'w')
sys.stderr = open(os.path.join(project_dir, 'exports', 'pythonw_crash.log'), 'w')

# Configuration
LOG_INTERVAL = 10  # Seconds

# Write a startup verification log
with open(os.path.join(project_dir, 'exports', 'startup_test.log'), 'w') as f:
    f.write("Tracker started at " + str(time.time()) + "\n")

class AttentionTracker:
    def __init__(self, api_url=None, session_id=None):
        self.api_url = api_url
        self.session_id = session_id
        self.project_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
        
        # Ensure exports dir exists
        os.makedirs(os.path.join(self.project_dir, 'exports'), exist_ok=True)
        
        # OpenCV Haar Cascades initialization
        # We use standard haarcascades that come with cv2
        casc_path = os.path.dirname(cv2.__file__) + "/data/"
        self.face_cascade = cv2.CascadeClassifier(casc_path + 'haarcascade_frontalface_default.xml')
        self.eye_cascade = cv2.CascadeClassifier(casc_path + 'haarcascade_eye.xml')
        
        self.running = False
        self.stats = []
        self.start_time = None

    def start_tracking(self):
        try:
            with open(os.path.join(self.project_dir, 'exports', 'pythonw_crash.log'), 'a') as f:
                f.write("Attempting to open camera 0 using DirectShow...\n")
                
            # Use CAP_DSHOW on Windows to prevent VideoCapture hanging in windowless pythonw.exe
            cap = cv2.VideoCapture(0, cv2.CAP_DSHOW)
            
            with open(os.path.join(self.project_dir, 'exports', 'pythonw_crash.log'), 'a') as f:
                f.write(f"Camera opened: {cap.isOpened()}\n")
                
            self.running = True
            self.start_time = time.time()
            last_log_time = self.start_time
            
            current_session_scores = []
            
            print(json.dumps({"status": "started", "message": "Camera tracking active (Haar)"}))
            with open(os.path.join(self.project_dir, 'exports', 'pythonw_crash.log'), 'a') as f:
                f.write(f"Tracking loop starting for session {self.session_id}\n")
            sys.stdout.flush()

        except Exception as e:
            with open(os.path.join(self.project_dir, 'exports', 'pythonw_crash.log'), 'a') as f:
                f.write(f"CRASH ON INIT: {str(e)}\n")
            return

        try:
            while self.running and cap.isOpened():
                success, image = cap.read()
                if not success:
                    continue

                gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
                # Detect faces
                faces = self.face_cascade.detectMultiScale(
                    gray,
                    scaleFactor=1.1,
                    minNeighbors=5,
                    minSize=(100, 100)
                )

                score = 0
                state = "Not Detected"

                if len(faces) > 0:
                    (x, y, w, h) = faces[0]
                    roi_gray = gray[y:y+h, x:x+w]
                    
                    eyes = self.eye_cascade.detectMultiScale(
                        roi_gray,
                        scaleFactor=1.1,
                        minNeighbors=3,
                        minSize=(20, 20)
                    )
                    
                    if len(eyes) >= 1:
                        score = 0.95
                        state = "Très Concentré"
                    else:
                        # Even if eyes aren't perfect, if face is there, they are likely working.
                        # Haar cascades often miss eyes due to glasses or looking slightly down.
                        score = 0.80 
                        state = "Concentré"
                        
                    current_session_scores.append(score)
                else:
                    # No face = completely distracted/absent
                    score = 0.0
                    state = "Absent/Distrait"
                    current_session_scores.append(score)

                # Write current status to a temporary file for the frontend to read
                status_file = os.path.join(self.project_dir, 'exports', f'status_{self.session_id}.json')
                try:
                    with open(status_file, 'w') as f:
                        json.dump({"state": state, "score": score}, f)
                except Exception:
                    pass

                # Periodic Logging (every 10s)
                if time.time() - last_log_time >= LOG_INTERVAL:
                    avg_score = sum(current_session_scores) / len(current_session_scores) if current_session_scores else 0
                    log_entry = {
                        "timestamp": time.time(),
                        "score": round(avg_score, 2),
                        "state": state
                    }
                    self.stats.append(log_entry)
                    print(json.dumps({"type": "periodic_log", "data": log_entry}))
                    sys.stdout.flush()
                    
                    current_session_scores = []
                    last_log_time = time.time()

                # Visual feedback (show the camera window to the user)
                # Commenting this out because the user wants it to be invisible
                # cv2.putText(image, f"Focus: {int(score*100)}% - {state}", (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0) if score > 0.7 else (0, 0, 255), 2)
                # cv2.imshow('Suivi IA Pomodoro', image)
                
                # Safety timeout (2 hours) to avoid zombie processes holding the camera forever
                if time.time() - self.start_time > 7200:
                    break

                # Check for stop signal file
                stop_file = os.path.join(self.project_dir, 'exports', f'stop_{self.session_id}.txt')
                if os.path.exists(stop_file):
                    if current_session_scores:
                        avg_score = sum(current_session_scores) / len(current_session_scores)
                        self.stats.append({
                            "timestamp": time.time(),
                            "score": round(avg_score, 2),
                            "state": state
                        })
                    break

                # Visual feedback (optional - for debugging if needed, but we run headless usually)
                # cv2.imshow('Attention Tracking', image)
                if cv2.waitKey(5) & 0xFF == 27:
                    break

        finally:
            self.stop_tracking(cap)

    def stop_tracking(self, cap):
        self.running = False
        if cap:
            cap.release()
        
        stop_file = os.path.join(self.project_dir, 'exports', f'stop_{self.session_id}.txt')
        if os.path.exists(stop_file):
            try:
                os.remove(stop_file)
            except:
                pass
                
        status_file = os.path.join(self.project_dir, 'exports', f'status_{self.session_id}.json')
        if os.path.exists(status_file):
            try:
                os.remove(status_file)
            except:
                pass

        # Calculate Final Stats
        if self.stats:
            final_avg = sum(s['score'] for s in self.stats) / len(self.stats)
        else:
            final_avg = 0
            
        summary = {
            "session_id": self.session_id,
            "average_focus": round(final_avg, 2),
            "duration_seconds": round(time.time() - self.start_time, 2),
            "logs": self.stats
        }
        
        print(json.dumps({"type": "final_summary", "data": summary}))
        sys.stdout.flush()
        
        # Send to API if provided
        if self.api_url:
            try:
                response = requests.post(self.api_url, json=summary, timeout=5, verify=False)
                if response.status_code != 200:
                    with open(os.path.join(self.project_dir, 'exports', 'tracker_error.log'), 'a') as f:
                        f.write(f"API Upload failed with status {response.status_code}: {response.text}\n")
            except Exception as e:
                # Log error for debugging
                with open(os.path.join(self.project_dir, 'exports', 'tracker_error.log'), 'a') as f:
                    f.write(f"API Request exception: {str(e)}\n")
                print(json.dumps({"type": "error", "message": f"API Request exception: {str(e)}"}))
                sys.stdout.flush()

if __name__ == "__main__":
    # Usage: python tracker.py [api_url] [session_id]
    api = sys.argv[1] if len(sys.argv) > 1 else None
    sid = sys.argv[2] if len(sys.argv) > 2 else None
    
    tracker = AttentionTracker(api_url=api, session_id=sid)
    tracker.start_tracking()
