Set WshShell = CreateObject("WScript.Shell")
WshShell.Run """C:\Users\azizr\studly/.venv/Scripts/python.exe"" ""C:\Users\azizr\studly\python_services\attention_tracking\tracker.py"" ""http://127.0.0.1:8000/temps/2/pomodoro/10/stats"" 10", 0, False
