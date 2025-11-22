<?php
declare(strict_types=1);

namespace App\Domain;

final class VehicleValidator
{
    /**
     * @param array{
     *   plate?:string,
     *   vehicle_type?:string,
     *   checkin?:string,
     *   checkout?:string
     * } $input
     * @return string[]
     */
    public function validate(array $input, bool $requireDates = false): array
    {
        $errors = [];

        $plate = strtoupper(trim((string)($input['plate'] ?? '')));
        $vehicleType = strtolower(trim((string)($input['vehicle_type'] ?? '')));

        if ($plate === '' || !preg_match('/^[A-Z0-9-]{5,10}$/', $plate)) {
            $errors[] = 'Placa inválida (use letras/números e hífen, 5-10 chars).';
        }

        $allowed = ['car', 'truck', 'motorcycle'];
        if (!in_array($vehicleType, $allowed, true)) {
            $errors[] = 'Tipo de veículo inválido.';
        }

        // Validação de datas apenas se necessário
        if ($requireDates) {
            $checkin = (string)($input['checkin'] ?? '');
            $checkout = (string)($input['checkout'] ?? '');

            $checkinDate = \DateTime::createFromFormat(\DateTime::ATOM, $checkin);
            if ($checkinDate === false) {
                $checkinDate = \DateTime::createFromFormat('Y-m-d\TH:i', $checkin);
            }
            if ($checkinDate === false) {
                $errors[] = 'Data/Hora de entrada inválida (use ISO 8601 ou formato datetime-local).';
            }

            $checkoutDate = \DateTime::createFromFormat(\DateTime::ATOM, $checkout);
            if ($checkoutDate === false) {
                $checkoutDate = \DateTime::createFromFormat('Y-m-d\TH:i', $checkout);
            }
            if ($checkoutDate === false) {
                $errors[] = 'Data/Hora de saída inválida (use ISO 8601 ou formato datetime-local).';
            }

            // Validar se checkout é posterior a checkin
            if ($checkinDate !== false && $checkoutDate !== false && $checkoutDate <= $checkinDate) {
                $errors[] = 'Data/Hora de saída deve ser posterior à data/hora de entrada.';
            }
        }

        return $errors;
    }
}
