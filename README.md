## Participatory Budget

Start project
```
docker-compose up -d
```

Install dependecies on backend
```
docker exec -it participatory_webapp composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --no-suggest --optimize-autoloader
```

Migrate database from Doctrine
```
docker exec -it participatory_webapp composer db-update
```

MinIO mirror

It is necessary to check whether there is no IP address conflict between the docker-compose pool and the server.
```
docker network inspect `docker network ls -q` | grep Subnet
```

```
docker run -it --rm --entrypoint=/bin/sh \
    --network=participatory_default \
    -v ./.certs/prod.intra.fph.hu.crt:/root/.mc/certs/prod.intra.fph.hu.crt \
    -v ./.certs/fph_corporate_root_ca2.crt:/root/.mc/certs/CAs/fph_corporate_root_ca2.crt \
    -v ./.certs/fph_corp_enterprise_issuer_ca2.crt:/root/.mc/certs/CAs/fph_corp_enterprise_issuer_ca2.crt \
    minio/mc

mc alias set prod https://participatory-minio.prod.intra.fph.hu <minio_username> <minio_password> --api s3v4
mc alias set local http://minio:9000 <minio_username> <minio_password> --api s3v4

mc mirror --watch prod/shared local/shared
```
