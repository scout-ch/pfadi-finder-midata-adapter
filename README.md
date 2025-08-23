# Pfadifinder Midata Adapter

This project fetches the data off midata and into a mysql database, where it is then used for the pfadi-finder on pfadi.swiss

## Getting Started

### Setup

1. Clone the repository.
2. Copy `src/config.example.php` to `src/config.php` and adjust environment variables if needed.
3. Start the services:

   ```sh
   docker-compose up --build
