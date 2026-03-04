Set WshShell = CreateObject("WScript.Shell")
WshShell.Run """c:\Users\jouin\studly\.venv\Scripts\python.exe"" ""C:\Users\jouin\studly\python_services\attention_tracking\tracker.py"" ""http://127.0.0.1:8000/temps/10/pomodoro/58/stats"" 58", 0, False
