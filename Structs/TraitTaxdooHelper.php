<?php

namespace VanWittlaerTaxdooConnector\Structs;

use DateTimeInterface;

trait TraitTaxdooHelper
{

    /**
     * @param array $data
     * @param string $field
     */
    private function setIfNotNull(array &$data, string $field)
    {
        if (isset($this->$field) && $this->$field instanceof DateTimeInterface) {
            ($this->$field === null) ?: $data[$field] = $this->$field->format(DATE_RFC3339);

            return;
        }
        (!isset($this->$field) || $this->$field === null) ?: $data[$field] = $this->$field;
    }

    /**
     * @param array $fields
     * @return array
     */
    private function filter(array $fields): array
    {
        $data = [];
        foreach ($fields as $field) {
            if (isset($this->$field) && $this->$field instanceof DateTimeInterface) {
                ($this->$field === null) ?: $data[$field] = $this->$field->format(DATE_RFC3339);

                continue;
            }
            (!isset($this->$field) || $this->$field === null) ?: $data[$field] = $this->$field;
        }

        return $data;
    }
}