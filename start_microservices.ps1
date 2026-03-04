# Start Microservices for Studly Project

echo "Starting Face Recognition Microservice on port 8001..."
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd face_service; python -m uvicorn main:app --port 8001 --reload"

echo "Starting Recommendation API on port 8002..."
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd recommendation_api; python -m uvicorn main:app --port 8002 --reload"

echo "Microservices are starting in background windows."
echo "Please wait a few seconds for the servers to initialize."
