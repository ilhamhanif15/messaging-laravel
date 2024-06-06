<?php

namespace App\Utils;

use Closure;
use DB;

class TryCatchUtil
{
    /**
     * @param Closure $callbackExecute
     * @param Closure|null $callbackIfError
     * @return mixed
     */
    public static function DBTransaction(Closure $callbackExecute, Closure $callbackIfError = null)
    {
        DB::beginTransaction();
        try {
            $ret = $callbackExecute();
            DB::commit();
            return $ret;
        } catch(\Exception $e) {
            DB::rollback();

            if ($callbackIfError !== null) {
                return $callbackIfError($e);
            }

            throw $e;
        }
    }
}
