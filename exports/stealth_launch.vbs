Set WshShell = CreateObject("WScript.Shell")
WshShell.Run """py"" ""C:\Users\azizr\studly\python_services\attention_tracking\tracker.py"" ""http://127.0.0.1:8000/temps/3/pomodoro/19/stats"" 19", 0, False
