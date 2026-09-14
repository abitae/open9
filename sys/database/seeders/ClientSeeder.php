<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientAddress;
use Database\Seeders\Concerns\SeedsReferenceImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    use SeedsReferenceImages;

    public function run(): void
    {
        $clients = [
            [
                'name' => 'Patricia Huamán',
                'email' => 'patricia.huaman@inmobiliarianorte.pe',
                'phone' => '+51 987 111 001',
                'avatar' => 'person-1',
                'city' => 'Lima',
                'region' => 'Lima',
                'line1' => 'Av. Javier Prado Este 4200',
                'line2' => 'Oficina 802, San Borja',
                'postal_code' => '15036',
            ],
            [
                'name' => 'Jorge Palacios',
                'email' => 'jorge@saboresdelsur.pe',
                'phone' => '+51 987 111 002',
                'avatar' => 'person-2',
                'city' => 'Lima',
                'region' => 'Lima',
                'line1' => 'Av. Caminos del Inca 2574',
                'line2' => 'Santiago de Surco',
                'postal_code' => '15023',
            ],
            [
                'name' => 'Dra. Elena Quispe',
                'email' => 'elena.quispe@clinicasanmartin.pe',
                'phone' => '+51 987 111 003',
                'avatar' => 'person-3',
                'city' => 'Lima',
                'region' => 'Lima',
                'line1' => 'Av. Arequipa 3456',
                'line2' => 'Miraflores',
                'postal_code' => '15074',
            ],
            [
                'name' => 'Marco Díaz',
                'email' => 'marco@contableplus.pe',
                'phone' => '+51 987 111 004',
                'avatar' => 'person-4',
                'city' => 'Lima',
                'region' => 'Lima',
                'line1' => 'Calle Las Begonias 475',
                'line2' => 'San Isidro, piso 6',
                'postal_code' => '15046',
            ],
            [
                'name' => 'María Elena Vargas',
                'email' => 'compras@comercioandes.pe',
                'phone' => '+51 987 111 005',
                'avatar' => 'person-5',
                'city' => 'Arequipa',
                'region' => 'Arequipa',
                'line1' => 'Calle Mercaderes 218',
                'line2' => 'Cercado de Arequipa',
                'postal_code' => '04001',
            ],
            [
                'name' => 'Ricardo Mendoza',
                'email' => 'ricardo@logisticapeak.pe',
                'phone' => '+51 987 111 006',
                'avatar' => 'person-6',
                'city' => 'Callao',
                'region' => 'Callao',
                'line1' => 'Av. Argentina 2850',
                'line2' => 'Almacén 12, Callao',
                'postal_code' => '07001',
            ],
            [
                'name' => 'Sofía Ramírez',
                'email' => 'sofia@fintechandina.pe',
                'phone' => '+51 987 111 007',
                'avatar' => 'person-7',
                'city' => 'Lima',
                'region' => 'Lima',
                'line1' => 'Av. República de Panamá 3455',
                'line2' => 'Torre Parque Mar, San Isidro',
                'postal_code' => '15047',
            ],
            [
                'name' => 'Carlos Vega',
                'email' => 'carlos@hotelcostaverde.pe',
                'phone' => '+51 987 111 008',
                'avatar' => 'person-8',
                'city' => 'Piura',
                'region' => 'Piura',
                'line1' => 'Av. Grau 1205',
                'line2' => 'Piura centro',
                'postal_code' => '20001',
            ],
            [
                'name' => 'Daniela Ortiz',
                'email' => 'daniela@academianova.pe',
                'phone' => '+51 987 111 009',
                'avatar' => 'person-9',
                'city' => 'Lima',
                'region' => 'Lima',
                'line1' => 'Av. La Marina 2855',
                'line2' => 'San Miguel',
                'postal_code' => '15087',
            ],
            [
                'name' => 'Andrés Castillo',
                'email' => 'andres@retailnova.pe',
                'phone' => '+51 987 111 010',
                'avatar' => 'person-10',
                'city' => 'Trujillo',
                'region' => 'La Libertad',
                'line1' => 'Av. España 1840',
                'line2' => 'Centro cívico, Trujillo',
                'postal_code' => '13001',
            ],
            [
                'name' => 'Lucía Fernández',
                'email' => 'lucia@urbanhomes.pe',
                'phone' => '+51 987 111 011',
                'avatar' => 'person-11',
                'city' => 'Lima',
                'region' => 'Lima',
                'line1' => 'Av. El Polo 670',
                'line2' => 'Santiago de Surco',
                'postal_code' => '15023',
            ],
            [
                'name' => 'Héctor Rojas',
                'email' => 'hector@grupobrasa.pe',
                'phone' => '+51 987 111 012',
                'avatar' => 'person-12',
                'city' => 'Cusco',
                'region' => 'Cusco',
                'line1' => 'Av. El Sol 920',
                'line2' => 'Wanchaq',
                'postal_code' => '08002',
            ],
        ];

        foreach ($clients as $index => $data) {
            $client = Client::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('Open9Cliente2026!'),
                    'phone' => $data['phone'],
                    'avatar' => $this->referenceImage($data['avatar'], 400, 400),
                    'google_id' => null,
                    'status' => 'active',
                    'email_verified_at' => now()->subDays(45 - $index),
                    'last_login_at' => now()->subDays($index + 1),
                ],
            );

            ClientAddress::query()->updateOrCreate(
                ['client_id' => $client->id, 'is_default' => true],
                [
                    'label' => 'Oficina principal',
                    'recipient_name' => $data['name'],
                    'phone' => $data['phone'],
                    'line1' => $data['line1'],
                    'line2' => $data['line2'],
                    'city' => $data['city'],
                    'region' => $data['region'],
                    'country' => 'PE',
                    'postal_code' => $data['postal_code'],
                    'is_default' => true,
                ],
            );
        }
    }
}
