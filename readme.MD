Este paquete supone que tienes un modelo Video en App\Models\Video con las columnas
 - code
 - cloud
 - thumbnail
 - status

El video tambien debe implementar varios mutators:
 - getS3OriginalPathAttribute
 - getS3HlsPathAttribute
 - getS3KeysPathAttribute
 - getS3HlsMasterAttribute
 - getNameAttribute
 - getUuidAttribute

Después esta clase pondrá el modelo, pero por ahora es así :-)

- Requiere tener queue de laravel configurado 
.env
`QUEUE_CONNECTION=database`

php artisan queue:table
php artisan migrate

php artisan queue:work

Configurar supervisor