"""Create admin and user API keys."""

from sqlmodel import Session, create_engine, delete
from app.db.database import create_db_and_tables, get_connection_url
from app.models import ApiKey


def seed_api_keys():
    """Seed database with API keys."""
    engine = create_engine(get_connection_url())

    with Session(engine) as session:
        # Delete all API keys
        statement = delete(ApiKey)
        session.exec(statement)
        session.commit()

        d = {
            'admin': [
                'admin-api-key',
            ],
            'user': [
                'user-api-key',
                'a-not-so-secret-key',
            ],
        }

        count = 0
        for role, keys in d.items():
            for key in keys:
                key_hash = ApiKey.hash_key(key)

                api_key = ApiKey(
                    name=f"API key #{count}",
                    description="",
                    key_hash=key_hash,
                    key_prefix=key[:8],
                    is_active=True,
                    role=role,
                )

                session.add(api_key)
                session.commit()
                session.refresh(api_key)

                print("-"*80)
                print(f"Name: {api_key.name}")
                print(f"Key:  {key}")
                print(f"Role: {api_key.role}")
                print("-"*80)

                count += 1

if __name__ == "__main__":
    create_db_and_tables()
    seed_api_keys()
