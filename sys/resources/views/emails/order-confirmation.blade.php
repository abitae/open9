<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de pedido</title>
</head>
<body style="margin:0;padding:0;background:#0b1120;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:600px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border-radius:16px;overflow:hidden;">
            <div style="background:#0077ff;padding:28px 32px;">
                <h1 style="margin:0;color:#ffffff;font-size:20px;">¡Pago confirmado!</h1>
                <p style="margin:6px 0 0;color:#e0ecff;font-size:13px;">Gracias por tu compra, {{ $order->buyer_name }}.</p>
            </div>

            <div style="padding:24px 32px;">
                <p style="font-size:14px;line-height:1.6;color:#334155;">
                    Recibimos tu pago correctamente. Este es el detalle de tu pedido
                    <strong>{{ $order->order_code }}</strong>.
                </p>

                <table style="width:100%;border-collapse:collapse;margin-top:16px;">
                    <thead>
                        <tr>
                            <th align="left" style="padding:8px 0;border-bottom:2px solid #e2e8f0;font-size:12px;color:#64748b;">Producto</th>
                            <th align="center" style="padding:8px 0;border-bottom:2px solid #e2e8f0;font-size:12px;color:#64748b;">Cant.</th>
                            <th align="right" style="padding:8px 0;border-bottom:2px solid #e2e8f0;font-size:12px;color:#64748b;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td style="padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#0f172a;">{{ $item->product_name }}</td>
                                <td align="center" style="padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#334155;">{{ $item->quantity }}</td>
                                <td align="right" style="padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#0f172a;">{{ number_format((float) $item->subtotal, 2) }} {{ $order->currency }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" align="right" style="padding:14px 8px 0;font-size:15px;font-weight:bold;color:#0f172a;">Total</td>
                            <td align="right" style="padding:14px 0 0;font-size:15px;font-weight:bold;color:#0077ff;">{{ number_format((float) $order->total, 2) }} {{ $order->currency }}</td>
                        </tr>
                    </tfoot>
                </table>

                <p style="font-size:13px;line-height:1.6;color:#64748b;margin-top:24px;">
                    Nuestro equipo coordinará contigo la entrega. Si tienes dudas, responde a este correo.
                </p>
            </div>
        </div>

        <p style="text-align:center;color:#64748b;font-size:11px;margin-top:16px;">
            Este es un mensaje automático de tu pedido en la tienda.
        </p>
    </div>
</body>
</html>
