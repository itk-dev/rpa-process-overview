"""Exceptions."""

from fastapi import HTTPException, status


class HTTPNotFoundException(HTTPException):
    """404 Not found exception."""

    def __init__(self, detail: any) -> None:
        """Create a new exception."""
        super().__init__(status_code=status.HTTP_404_NOT_FOUND, detail=detail)


class UpdateError(Exception):
    """Update error."""
