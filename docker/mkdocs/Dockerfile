FROM python:3.12-slim-bookworm

WORKDIR /var/www

COPY docs/requirements.txt .

RUN pip install --no-cache-dir -r requirements.txt

WORKDIR /var/www/html

CMD ["mkdocs", "serve", "--dev-addr=0.0.0.0:8000"]
