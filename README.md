Steps to do:

If not already cloned
```
git clone https://github.com/SkyBlueFox/CS-Cloth.git
cd CS-Cloth
```

If already cloned
```
git checkout develop
git flow init
cp .env.example .env
```

then edit .env file
```
DB_DATABASE=cs_cloth
REDIS_PREFIX=""
```

then
```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/app" \
    -w /app \
    composer:latest \
    install --ignore-platform-reqs
```

then
```aiignore
sail up -d
sail artisan key:generate
sail artisan migrate:fresh --seed
```

## Login Credentials (For Testing)
**Super Admin:**
- Email: tan@cloth.com
- Password: asdf1234

