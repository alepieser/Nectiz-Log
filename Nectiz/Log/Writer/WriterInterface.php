<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alex
 * Date: 08/05/13
 * Time: 03:34
 * To change this template use File | Settings | File Templates.
 */

namespace Nectiz\Log\Writer;


interface WriterInterface
{
    public function isWriting(array $log);

    public function writeBatch(array $logs);

    public function write(array $log);

    public function close();

}
