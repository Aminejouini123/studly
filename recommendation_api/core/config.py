import os
from pydantic_settings import BaseSettings
from dotenv import load_dotenv

env_path = os.path.join(os.path.dirname(__file__), '..', '..', '.env')
load_dotenv(env_path, override=True)

class Settings(BaseSettings):
    openrouter_api_key: str = "your_openrouter_api_key_here"
    openrouter_model: str = "openai/gpt-4o-mini"
    
    # Udemy API (Requires base64 encoding of client_id:client_secret or just passing them)
    udemy_client_id: str = ""
    udemy_client_secret: str = ""
    
    # Coursera API
    coursera_client_id: str = ""
    coursera_client_secret: str = ""
    coursera_access_token: str = ""
    
    # Adzuna API (Optional, using Remotive as default for Jobs)
    adzuna_app_id: str = ""
    adzuna_app_key: str = ""
    
    class Config:
        env_file = env_path
        env_file_encoding = "utf-8"
        extra = "ignore"
        
settings = Settings()
