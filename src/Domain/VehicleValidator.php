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

        // Remove hífen para validação
        $plateWithoutHyphen = str_replace('-', '', $plate);
        
        // Validação: LLLNLNN (Mercosul) ou LLLNNNN (antigo)
        // Onde L é letra e N é número
        $isValidPlate = false;
        if (strlen($plateWithoutHyphen) === 7) {
            // Formato Mercosul: LLLNLNN (3 letras, 1 número, 1 letra, 2 números)
            if (preg_match('/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/', $plateWithoutHyphen)) {
                $isValidPlate = true;
            }
            // Formato antigo: LLLNNNN (3 letras, 4 números)
            elseif (preg_match('/^[A-Z]{3}[0-9]{4}$/', $plateWithoutHyphen)) {
                $isValidPlate = true;
            }
        }
        
        if ($plate === '' || !$isValidPlate) {
            $errors[] = 'Placa inválida. Use o formato LLLNLNN (Mercosul) ou LLLNNNN (antigo).';
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
