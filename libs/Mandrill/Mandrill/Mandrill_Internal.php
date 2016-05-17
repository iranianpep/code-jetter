<?php

    namespace CodeJetter\libs\Mandrill\Mandrill;

    use CodeJetter\libs\Mandrill\Mandrill;

class Mandrill_Internal {
    public function __construct(Mandrill $master) {
        $this->master = $master;
    }

}


