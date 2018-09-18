<?php

namespace AppBundle\Traits;

/**
 * Interface IdentifiableTraitIntertface
 */
interface IdentifiableEntityInterface
{
    /**
     * @param mixed $id
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getId();
}