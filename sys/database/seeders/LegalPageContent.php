<?php

namespace Database\Seeders;

class LegalPageContent
{
    /**
     * Textos plantilla de las páginas legales públicas.
     *
     * @return array<string, array{title: string, blocks: list<array{type: string, content: string}>}>
     */
    public static function all(): array
    {
        return [
            'terminos' => [
                'title' => 'Términos y Condiciones',
                'blocks' => [
                    ['type' => 'heading', 'content' => '1. Aceptación'],
                    ['type' => 'paragraph', 'content' => 'Estos términos regulan el uso del sitio open9.dev, la tienda en línea y la contratación de servicios de automatización, software e inteligencia artificial de OPEN9. Al navegar, crear una cuenta o realizar un pedido aceptas estas condiciones. Si no estás de acuerdo, no uses el sitio.'],
                    ['type' => 'heading', 'content' => '2. Identificación'],
                    ['type' => 'paragraph', 'content' => 'OPEN9 opera desde Lima, Perú, con atención remota en todo el país. Para contacto comercial o legal: empresario.ia@open9.dev y +51 999 000 009.'],
                    ['type' => 'heading', 'content' => '3. Cuenta de cliente'],
                    ['type' => 'paragraph', 'content' => 'Puedes registrarte con correo y contraseña o con Google. El registro por formulario requiere confirmar un código enviado a tu correo antes de usar la cuenta, el checkout autenticado o el historial de pedidos. Eres responsable de la confidencialidad de tus credenciales y de la veracidad de los datos que nos entregas.'],
                    ['type' => 'heading', 'content' => '4. Tienda y pedidos'],
                    ['type' => 'paragraph', 'content' => 'Los precios, stock y disponibilidad se confirman en nuestros sistemas al crear el pedido; no se aceptan montos enviados por el navegador. Un pedido queda pendiente hasta que MercadoPago reporte el pago aprobado. Podemos rechazar o cancelar pedidos por stock insuficiente, datos incompletos o indicios de fraude.'],
                    ['type' => 'heading', 'content' => '5. Pagos'],
                    ['type' => 'paragraph', 'content' => 'Los cobros en línea se procesan a través de MercadoPago (tarjetas, Yape u otros medios que habilite la pasarela). OPEN9 no almacena los datos completos de tu tarjeta. El comprobante y el detalle del pedido se envían al correo del comprador cuando el pago queda aprobado.'],
                    ['type' => 'heading', 'content' => '6. Entregas y servicios'],
                    ['type' => 'paragraph', 'content' => 'Tras un pago correcto coordinamos la entrega del producto o la prestación del servicio con los datos de contacto y dirección que nos diste. Los plazos se confirman caso a caso. Los servicios de automatización o software a medida se rigen además por la propuesta o contrato particular, si existe.'],
                    ['type' => 'heading', 'content' => '7. Propiedad intelectual'],
                    ['type' => 'paragraph', 'content' => 'Marcas, textos, diseños, código y contenidos del sitio pertenecen a OPEN9 o a sus licenciantes. No puedes copiarlos, revenderlos ni usarlos para competir con nosotros sin autorización escrita.'],
                    ['type' => 'heading', 'content' => '8. Responsabilidad'],
                    ['type' => 'paragraph', 'content' => 'El sitio se ofrece «tal cual». OPEN9 no responde por interrupciones de red, de MercadoPago o de terceros, ni por daños indirectos. Nuestra responsabilidad máxima por un pedido se limita al importe efectivamente pagado en ese pedido.'],
                    ['type' => 'heading', 'content' => '9. Ley aplicable'],
                    ['type' => 'paragraph', 'content' => 'Estos términos se rigen por las leyes de la República del Perú. Cualquier controversia se somete a los jueces y tribunales de Lima, salvo norma imperativa en contrario.'],
                    ['type' => 'heading', 'content' => '10. Cambios'],
                    ['type' => 'paragraph', 'content' => 'Podemos actualizar estos términos. La versión vigente es la publicada en esta página. El uso posterior del sitio implica aceptación de los cambios.'],
                ],
            ],
            'privacidad' => [
                'title' => 'Política de Privacidad',
                'blocks' => [
                    ['type' => 'heading', 'content' => '1. Responsable del tratamiento'],
                    ['type' => 'paragraph', 'content' => 'OPEN9 es responsable del tratamiento de los datos personales recabados en open9.dev. Contacto: empresario.ia@open9.dev. Esta política se formula en el marco de la Ley N.° 29733, Ley de Protección de Datos Personales, y su reglamento.'],
                    ['type' => 'heading', 'content' => '2. Datos que recopilamos'],
                    ['type' => 'paragraph', 'content' => 'Cuenta: nombre, correo, teléfono opcional, contraseña (almacenada de forma irreversible) o identificador de Google si inicias sesión con ese proveedor. Pedidos: datos del comprador, dirección de envío, productos, importes y referencias de pago de MercadoPago. Contacto y boletín: los datos que envíes en esos formularios. Datos técnicos mínimos de navegación necesarios para operar el sitio.'],
                    ['type' => 'heading', 'content' => '3. Acceso con Google'],
                    ['type' => 'paragraph', 'content' => 'Si eliges Google, recibimos el identificador, nombre, correo y foto de perfil que el proveedor nos entrega para crear o vincular tu cuenta. No usamos tu cuenta de Google para publicar en tu nombre.'],
                    ['type' => 'heading', 'content' => '4. Finalidades'],
                    ['type' => 'paragraph', 'content' => 'Tratamos los datos para crear y verificar tu cuenta, procesar pedidos y pagos, enviarte la confirmación de compra, coordinar entregas, atender soporte, cumplir obligaciones legales y, si lo aceptas, mejorar la medición del sitio. No vendemos tus datos.'],
                    ['type' => 'heading', 'content' => '5. Conservación'],
                    ['type' => 'paragraph', 'content' => 'Conservamos la cuenta mientras esté activa y los pedidos el tiempo exigido por la normativa tributaria y de consumo aplicable. Puedes solicitar la baja de la cuenta; algunos registros de facturación pueden conservarse por obligación legal.'],
                    ['type' => 'heading', 'content' => '6. Encargados y destinatarios'],
                    ['type' => 'paragraph', 'content' => 'Compartimos datos estrictamente necesarios con MercadoPago (cobros), el proveedor de correo transaccional, el hosting y, si corresponde, el servicio de autenticación de Google. Estos terceros tratan los datos según sus propias políticas y la normativa aplicable.'],
                    ['type' => 'heading', 'content' => '7. Derechos ARCO'],
                    ['type' => 'paragraph', 'content' => 'Puedes solicitar acceso, rectificación, cancelación, oposición y revocación del consentimiento escribiendo a empresario.ia@open9.dev. Atenderemos la solicitud en los plazos legales. También puedes acudir a la Autoridad Nacional de Protección de Datos Personales.'],
                    ['type' => 'heading', 'content' => '8. Seguridad'],
                    ['type' => 'paragraph', 'content' => 'Aplicamos medidas razonables (cifrado de credenciales, verificación de correo, firma de pedidos y de webhooks de pago) para reducir riesgos de acceso o alteración indebida. Ningún sistema es infalible; te pedimos proteger tu contraseña y el acceso a tu correo.'],
                    ['type' => 'heading', 'content' => '9. Cookies'],
                    ['type' => 'paragraph', 'content' => 'El detalle de cookies y de cómo gestionar tu consentimiento está en la Política de Cookies.'],
                ],
            ],
            'cookies' => [
                'title' => 'Política de Cookies',
                'blocks' => [
                    ['type' => 'heading', 'content' => '1. Qué usamos'],
                    ['type' => 'paragraph', 'content' => 'En OPEN9 usamos cookies y almacenamiento local para hacer funcionar el sitio. Distinguimos cookies técnicas (siempre activas, porque el sitio no opera sin ellas) y cookies no esenciales de medición, que solo se activan si aceptas el banner de consentimiento.'],
                    ['type' => 'heading', 'content' => '2. Cookies y datos técnicos necesarios'],
                    ['type' => 'paragraph', 'content' => 'Sesión del panel de administración (Laravel). Cookie oauth_state, de un solo uso, para completar el acceso con Google de forma segura. En el navegador, el carrito (open9_cart) y el token de cliente (open9_client_token) en almacenamiento local, necesarios para comprar e iniciar sesión. La preferencia del banner (open9_cookie_consent) se guarda para recordar tu elección.'],
                    ['type' => 'heading', 'content' => '3. Cookies no esenciales'],
                    ['type' => 'paragraph', 'content' => 'Si aceptas, podremos cargar herramientas de medición o mejora de la experiencia. Hoy el sitio puede no tener scripts de analítica activos; cuando se incorporen, respetarán esta elección. Si eliges «Solo técnicas», no cargaremos esas herramientas.'],
                    ['type' => 'heading', 'content' => '4. Cómo cambiar tu elección'],
                    ['type' => 'paragraph', 'content' => 'Puedes pulsar «Cambiar preferencia de cookies» en esta página para volver a ver el banner y aceptar o limitar el uso. También puedes borrar cookies y datos del sitio desde la configuración de tu navegador; en ese caso te pediremos de nuevo tu elección.'],
                    ['type' => 'heading', 'content' => '5. Más información'],
                    ['type' => 'paragraph', 'content' => 'El tratamiento de datos personales se describe en la Política de Privacidad. Consultas: empresario.ia@open9.dev.'],
                ],
            ],
        ];
    }
}
