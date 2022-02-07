<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\ValueObject\Amount;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;
use InvalidArgumentException;

class NationalNotSubjectBreakdownItem implements TbaiXml
{
    const NOT_SUBJECT_REASON_RL = 'RL';
    const NOT_SUBJECT_REASON_LOCATION_RULES = 'RL';
    const NOT_SUBJECT_REASON_OTHER = 'OT';
    const NOT_SUBJECT_REASON_OT = 'OT';

    private string $notSubjectReason;
    private string $amount;

    public function __construct(Amount $amount, string $reason)
    {
        $this->amount = $amount;
        $this->setNotSubjectReason($reason);
    }

    private static function validNotSubjectReasons(): array
    {
        return [
            self::NOT_SUBJECT_REASON_RL,
            self::NOT_SUBJECT_REASON_OT,
        ];
    }

    private function setNotSubjectReason(string $reason): self
    {
        if (!in_array($reason, self::validNotSubjectReasons())) {
            throw new InvalidArgumentException('Subject reason is not valid');
        }
        $this->notSubjectReason = $reason;

        return $this;
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $notSubjectDetail = $domDocument->createElement('DetalleNoSujeta');
        $notSubjectDetail->append(
            $domDocument->createElement('Causa', $this->notSubjectReason),
            $domDocument->createElement('Importe', $this->amount),
        );
        return $notSubjectDetail;
    }

    public static function createFromJson(array $jsonData): self
    {
        $amount = new Amount($jsonData['amount']);
        $reason = $jsonData['reason'];
        return new self($amount, $reason);
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'amount' => [
                    'type' => 'string',
                    'description' => 'Zenbatekoa (2 dezimalekin) - Importe (2 decimales)'
                ],
                'reason' => [
                    'type' => 'string',
                    'enum' => self::validNotSubjectReasons(),
                    'description' => '
Kargapean ez egoteko arrazoia - Causa no sujeción:
  * RL: Kargapean ez kokapen arauak direla eta - No sujeto por reglas de localización
  * OT: Kargapean ez 7., 14. art, Beste batzuk - No sujeto art. 7, 14, Otros
'
                ],
            ],
            'required' => ['amount', 'reason']
        ];
    }
}
