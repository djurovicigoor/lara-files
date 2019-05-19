<?php
/**
 * Created by PhpStorm.
 * User: djurovic
 * Date: 2019-04-18
 * Time: 10:28
 */

namespace DjurovicIgoor\LaraFiles\Traits;

trait HashNameTrait
{
    /**
     * @return string
     */
    public function generateHashName()
    {
        return $this->laraFile->hash_name = md5(microtime());
    }
}
