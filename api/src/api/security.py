import json
import os

from dotenv import dotenv_values
from fastapi import HTTPException, Security, status
from fastapi.security import APIKeyHeader

config = {
    **dotenv_values(".env.local"),  # load shared development variables
    **os.environ,  # override loaded values with environment variables
}
try:
    API_KEYS_READ = json.loads(config.get('API_KEYS_READ', '[]'))
except json.JSONDecodeError:
    API_KEYS_READ = []

try:
    API_KEYS_WRITE = json.loads(config.get('API_KEYS_WRITE', '[]'))
except json.JSONDecodeError:
    API_KEYS_WRITE = []

api_key_header = APIKeyHeader(name="x-api-key", auto_error=False)


def get_api_key(
    api_key_header: str = Security(api_key_header),
) -> str:
    """Retrieve and validate an API key from the query parameters or HTTP header.

    Args:
        api_key_header: The API key passed in the HTTP header.

    Returns:
        The validated API key.

    Raises:
        HTTPException: If the API key is invalid or missing.
    """
    if api_key_header in API_KEYS_READ:
        return api_key_header
    raise HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Invalid or missing API Key",
    )


def get_api_key_write(
    api_key_header: str = Security(api_key_header),
) -> str:
    """Retrieve and validate an API key from the query parameters or HTTP header.

    Args:
        api_key_header: The API key passed in the HTTP header.

    Returns:
        The validated API key.

    Raises:
        HTTPException: If the API key is invalid or missing.
    """
    if api_key_header in API_KEYS_WRITE:
        return api_key_header
    raise HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Invalid or missing API Key",
    )
