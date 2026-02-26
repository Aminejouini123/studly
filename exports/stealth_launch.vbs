Set WshShell = CreateObject("WScript.Shell")
WshShell.Run """python"" ""C:\Users\azizr\studly\python_services\attention_tracking\tracker.py"" ""http://127.0.0.1:8000/temps/2/pomodoro/13/stats"" 13", 0, False
