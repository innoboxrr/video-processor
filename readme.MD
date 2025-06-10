# INSTALACION

## CloudFrontService

Perfecto Raúl. A continuación te detallo exactamente **qué opciones debes seleccionar al crear una nueva distribución de CloudFront** para entregar **contenido HLS (.m3u8/.ts) con URLs firmadas usando el sistema moderno** (Public Keys y Key Groups).

---

## 🧭 PASO A PASO PARA CREAR TU DISTRIBUCIÓN CLOUDFRONT SEGURA

---

### 🔹 1. **Distribution options**

* ✅ **Selecciona:** `Single website or app`

---

### 🔹 2. **Origin**

* **Origin domain**: Si tu video está en un bucket S3 privado:

  ```
  nombre-del-bucket.s3.amazonaws.com
  ```

  > ⚠️ Asegúrate de que el bucket esté **privado** y tengas habilitado CloudFront como método de acceso.

* **Origin path (opcional)**: Deja vacío, a menos que todos tus videos estén en un subdirectorio como `/videos`.

* **Name**: Ponle un nombre descriptivo, como `s3-origin-videos`.

* **Add custom header**: No es necesario.

* **Enable Origin Shield**:
  ❌ No

---

### 🔹 3. **Default cache behavior**

* **Path pattern**: Default `(*)`

* **Compress objects automatically**:
  ✅ `Yes`

#### **Viewer**

* **Viewer protocol policy**:
  ✅ `Redirect HTTP to HTTPS`

* **Allowed HTTP methods**:
  ✅ `GET, HEAD` (solo necesitas servir archivos)

* **Restrict viewer access**:
  ✅ `Yes` → Esto es **esencial** para usar URLs firmadas.

#### **Cache key and origin requests**

* ✅ `Cache policy and origin request policy (recommended)`

  * **Cache policy**:
    Selecciona: `CachingOptimized`

  * **Origin request policy**:
    Selecciona: `AllViewerExceptHostHeader` o crea uno nuevo si necesitas headers especiales.

  * **Response headers policy**:
    Opcional. Puedes dejarlo vacío.

---

### 🔹 4. **Function associations**

* ❌ No necesitas configurar funciones en este momento.

---

### 🔹 5. **Web Application Firewall (WAF)**

* ✅ Puedes seleccionar `Do not enable` (puedes agregarlo luego si necesitas protección).

---

### 🔹 6. **Price class**

* ✅ Puedes seleccionar según tu público:

  * `Use only North America and Europe` si solo tienes usuarios en esas regiones
  * `Use all edge locations` para mejor rendimiento global

---

### 🔹 7. **Alternate domain name (CNAME)** (opcional)

* Si vas a usar un dominio propio, agrégalo aquí (ej. `videos.tudominio.com`)
* Si no, deja vacío

---

### 🔹 8. **Custom SSL certificate**

* Si usaste un CNAME, aquí debes **seleccionar el certificado SSL** desde AWS Certificate Manager.
* Si no tienes uno aún, haz clic en **"Request certificate"**

> El certificado debe estar en la región `us-east-1` (Virginia del Norte).

---

### 🔹 9. **Supported HTTP versions**

* ✅ Marca `HTTP/2` y `HTTP/3` (mejora de rendimiento)

---

### 🔹 10. **Default root object**

* ❌ Déjalo vacío (no aplica para contenido HLS)

---

### 🔹 11. **IPv6**

* ✅ `On`

---

### 🔹 12. **Standard logging**

* ❌ `Off` (puedes activarlo después si deseas auditar tráfico)

---

## ✅ FINALIZA

Haz clic en **Create distribution**.

Una vez creada:

* Ve a **Behaviors > Edit**
* En la sección de seguridad, asocia el **Key Group** que creaste anteriormente.

### kEY MANAGEMENT

#### 1. Genera tu par de claves (privada y pública RSA)

En tu máquina local, corre:

```bash
openssl genrsa -out private_key.pem 2048
openssl rsa -pubout -in private_key.pem -out public_key.pem
```

Esto te dará:
* `private_key.pem`: la usarás para firmar las URLs.
* `public_key.pem`: la subirás a AWS.

#### 2. Crea la clave pública en AWS

1. Ve a [CloudFront > Key Management > Public Keys](https://console.aws.amazon.com/cloudfront/v3/home#/public-keys)
2. Haz clic en **"Create public key"**
3. Ponle un nombre y pega el contenido de `public_key.pem`
4. Guarda el `ID` de la clave pública (ejemplo: `K123ABC456XYZ`)

#### 3. Crea un **Key Group**

1. Ve a **CloudFront > Key Groups**
2. Haz clic en **"Create key group"**
3. Ponle nombre
4. Selecciona la clave pública que creaste antes
5. Guarda el **ID del Key Group** (ejemplo: `abcd1234-keygroup`)

#### 4. Asocia el Key Group a tu distribución de CloudFront

1. Ve a tu distribución de CloudFront
2. En **Behaviors > Edit**, busca la sección **Restrict viewer access (signed URLs or signed cookies)**
3. Selecciona **Yes**
4. En "Trusted key groups", agrega el Key Group creado

Guarda los cambios.

---

#### 5. Guarda tu clave privada en Laravel

Guarda `private_key.pem` en tu proyecto, por ejemplo en:

```
storage/cloudfront/private_key.pem
```

---

#### 6. Agrega configuración en `.env`

```
CLOUDFRONT_DOMAIN=https://tudistribucion.cloudfront.net
CLOUDFRONT_PUBLIC_KEY_ID=K123ABC456XYZ
CLOUDFRONT_PRIVATE_KEY_PATH=storage/cloudfront/private_key.pem
CLOUDFRONT_URL_EXPIRATION=240
```

---

#### 7. Agrega la configuración a `config/videoprocessor.php`

```php
'cloudfront' => [
    'domain' => env('CLOUDFRONT_DOMAIN'),
    'public_key_id' => env('CLOUDFRONT_PUBLIC_KEY_ID'),
    'private_key_path' => base_path(env('CLOUDFRONT_PRIVATE_KEY_PATH')),
    'url_expiration' => env('CLOUDFRONT_URL_EXPIRATION', 240),
],
```

---

#### 8. Lógica para generar una URL firmada

Aquí tienes una clase de servicio compatible con el sistema moderno:

```php
use Carbon\Carbon;

class ModernCloudFrontService
{
    public function generateSignedUrl(string $resourceUrl): string
    {
        $expires = Carbon::now()->addMinutes(config('videoprocessor.cloudfront.url_expiration'))->timestamp;

        $policy = json_encode([
            'Statement' => [[
                'Resource' => $resourceUrl,
                'Condition' => [
                    'DateLessThan' => ['AWS:EpochTime' => $expires],
                ],
            ]],
        ]);

        $privateKey = file_get_contents(config('videoprocessor.cloudfront.private_key_path'));

        openssl_sign($policy, $signature, $privateKey, OPENSSL_ALGO_SHA1);
        $encodedPolicy = strtr(base64_encode($policy), ['+' => '-', '=' => '_', '/' => '~']);
        $encodedSignature = strtr(base64_encode($signature), ['+' => '-', '=' => '_', '/' => '~']);

        return $resourceUrl
            . '?Policy=' . $encodedPolicy
            . '&Signature=' . $encodedSignature
            . '&Key-Pair-Id=' . config('videoprocessor.cloudfront.public_key_id');
    }
}
```

---

#### 9. Cómo usarla

```php
$service = new ModernCloudFrontService();
$url = $service->generateSignedUrl('https://tudistribucion.cloudfront.net/path/to/video/index.m3u8');
return redirect()->away($url);
```

---

#### ✅ Resultado

El sistema generará URLs como:

```
https://yourcdn.cloudfront.net/video/abc/index.m3u8?Policy=...&Signature=...&Key-Pair-Id=K123ABC456XYZ
```

Y solo podrán reproducirse si son válidas y no han expirado.

---

## Notificaciones de SNS para el procesamiento de MediaConverter 

✅ 1. Crea un SNS Topic en AWS
Ve a AWS SNS.

Crea un Topic de tipo Standard.

Dale un nombre (ej. MediaConvertJobNotifications).

Guarda el ARN del topic (lo usarás en MediaConvert).

# TODO

1. AutoGenerateTranslatedVttRequest & UploadTranslatedVttRequest usan el modelo Language
    - No debería ser así, ya que es una acoplamiento con la app principal
2. Hacer que el paquete use su propio modelo Video yque tenga un trait para todos los mutators y relaciones
3. Ver si este paquete asume el modelo Languaje & Subtitles dentro de su misma estructura.