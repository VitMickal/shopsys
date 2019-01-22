<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations\DataModifiers;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_values;

class CountryDataModifierVersion20190121094400
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $tmpIds;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->tmpIds = [];
    }

    /**
     * @return array
     */
    public function getGroupedByCode(): array
    {
        $tmp = [];
        foreach ($this->data as $row) {
            $tmp[$row['code']][] = $row;
        }

        return $tmp;
    }

    /**
     * @return array
     */
    public function getNewIdCodePair(): array
    {
        $tmp = [];
        foreach ($this->data as $row) {
            if ($row['domain_id'] !== 1) {
                continue;
            }

            $tmp[$row['code']] = $row['id'];
        }

        foreach ($this->data as $row) {
            if ($row['domain_id'] === 1 || array_key_exists($row['code'], $tmp)) {
                continue;
            }

            $tmp[$row['code']] = $row['id'];
        }

        return $tmp;
    }

    /**
     * @return array
     */
    public function getAllCodes(): array
    {
        return array_keys($this->getNewIdCodePair());
    }

    /**
     * @return array
     */
    public function getAllIds(): array
    {
        $tmp = [];
        foreach ($this->data as $row) {
            $tmp[$row['id']] = $row['id'];
        }

        return $tmp;
    }

    /**
     * @param int $oldId
     * @return int
     */
    public function getNewId(int $oldId): int
    {
        if (empty($this->tmpIds)) {
            $this->loadIdPairs();
        }

        return $this->tmpIds[$oldId];
    }

    private function loadIdPairs(): void
    {
        $pair = $this->getNewIdCodePair();

        foreach ($this->data as $row) {
            $this->tmpIds[$row['id']] = $pair[$row['code']];
        }
    }

    /**
     * @param $domainId
     * @param $countryCode
     * @return bool
     */
    private function codeExistsForDomain($domainId, $countryCode): bool
    {
        foreach ($this->data as $row) {
            if ($row['code'] === $countryCode && $row['domain_id'] === $domainId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $domainId
     * @param string $countryCode
     * @return array
     */
    public function getDomainDataForCountry(int $domainId, string $countryCode): array
    {
        $codeIdPairs = $this->getNewIdCodePair();

        return [
            'country_id' => $codeIdPairs[$countryCode],
            'domain_id' => $domainId,
            'enabled' => $this->codeExistsForDomain($domainId, $countryCode),
            'priority' => 0,
        ];
    }

    /**
     * @param int $domainId
     * @param string $countryCode
     * @return array
     */
    public function getTranslatableDataForCountry(int $domainId, string $countryCode): array
    {
        $codeIdPairs = $this->getNewIdCodePair();

        return [
            'translatable_id' => $codeIdPairs[$countryCode],
            'name' => $this->getNameForCountryAndDomain($domainId, $countryCode),
        ];
    }

    /**
     * @param int $domainId
     * @param string $countryCode
     * @return string
     */
    private function getNameForCountryAndDomain(int $domainId, string $countryCode): string
    {
        foreach ($this->data as $row) {
            if ($row['code'] === $countryCode && $row['domain_id'] === $domainId) {
                return $row['name'];
            }
        }

        return $countryCode;
    }

    /**
     * @return array
     */
    public function getObsoleteCountryIds(): array
    {
        $obsoleteIds = [];
        foreach ($this->data as $row) {
            $obsoleteIds[] = $row['id'];
        }

        $usedIds = array_values($this->getNewIdCodePair());

        return array_values(array_diff($obsoleteIds, $usedIds));
    }
}
