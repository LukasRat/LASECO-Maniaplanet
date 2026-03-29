# LASECO (LukasRat's Advanced Server Environment Control Organizer)

LASECO is a highly modified, deeply rebranded, and PHP 8.3-optimized fork of the original [UASECO](https://github.com/undeflabs/UASECO-Maniaplanet) trackmania controller.

## Features
- **Full PHP 8.3 Support**: Dynamic properties and deprecated functions have been carefully suppressed or migrated for modern stability.
- **Dockerized Base**: A production-ready `Dockerfile` and `docker-entrypoint.sh` guarantee smooth boot alignments with dedicated Trackmania servers and MariaDB backends.
- **Customizations**: Fully tailored author tags (`LukasRat`), localized messages, and modernized plugin fixes (e.g., `mania_karma` initialization failures, `records_eyepiece` schema constraints).

## Running via GHCR
You do not need to build this image manually unless customizing the core engine. Simply pull the latest image from the GitHub Container Registry.

### `docker-compose.yml` Target Template
```yaml
services:
  uaseco:
    image: ghcr.io/LukasRat/laseco:latest
    restart: unless-stopped
    depends_on:
      - dedicated
      - database
    environment:
      - DEDICATED_HOST=dedicated
      - DEDICATED_PORT=5000
      - DB_HOST=database
      - DB_PORT=3306
    volumes:
      # Optional Config/Plugin live overrides: 
      # - ./uaseco-config/:/app/uaseco/config/
      # - ./plugins/:/app/uaseco/plugins/
      - ./Logs/:/app/uaseco/logs/
      - ./Backups/:/app/uaseco/cache/
      - ./Config/:/app/dedicated/UserData/Config/
      - ./Maps/:/app/dedicated/UserData/Maps/
      - /PacksManiaPlanet/:/app/dedicated/UserData/Packs/
```

### Developing Locally
```bash
docker build -t ghcr.io/LukasRat/laseco:latest .
```

## Support 
[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/P5P81TXQBY)
