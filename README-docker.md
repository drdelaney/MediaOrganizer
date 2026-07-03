# About

This document is for pointing users to a simple docker setup which can use SQLite3 out of the box.

This will evolve over time, but for now, this is a basic environment to get someone up and working.

This assumes you have an understanding of docker, linux, and basic text editors and troubleshooting of your own applications.

This comes with no support.

# Git and Docker configuration and environment

## Create the docker structure

I personally like placing my files under /opt/docker, but you can edit the files and change this as needed.

```bash
sudo mkdir -p /opt/doocker/mediaorganizer/writable
touch /opt/docker/mediaorganizer/.env
```

## Git clone

```bash
cd; mkdir -p git; cd git
git clone https://github.com/drdelaney/MediaOrganizer.git
```

If you want to copy over the .env file you can do so now, otherwise the docker image will copy over the example at setup.
Feel free to edit this file now.
Note: currently app.baseURL and auth.initialPassword will be overwritten at every run of the docker instance as they are set in the docker-compose file. This may change in the future.

```bash
sudo cp ~/git/MediaOrganizer/.env.example /opt/docker/mediaorganizer/.env
```

## Docker

```bash
cd ~/git/MediaOrganizer

# Edit the docker-compose.yml as needed for names, destiations, ports and settings

# This will take a little bit of time
docker compose up -d --build --force-recreate
```

## Accessing the site

If no changes were made to the file, you should be able to access the site at http://127.0.0.1:8080

If not, check docker logs

```bash
docker compose ps
docker compose logs -f

ls -la /opt/docker/mediaorganizer/writable/logs/
tail -f /opt/docker/mediaorganizer/writable/logs/log-*.log

```

## Initial configuration

Once you load the page, it will ask you to import the database settings, unless you provide a database that exists already (advanced, not defined here).
After submission, you will need to edit the .env file and change a single line to mark the handling complete.

```bash
docker compose down
nano /opt/docker/mediaorganizer/.env
  # Edit the line app.setupComplete to read:
  app.setupComplete=true
docker compose -d
```

Now reload the page and it should be active.

## Rebuilding the docker image

From time to time (say an update), you will need to rebuild the data in the docker file

```bash
cd ~/git/MediaOrganizer
docker compose down
docker compose up -d --build --force-recreate
```
