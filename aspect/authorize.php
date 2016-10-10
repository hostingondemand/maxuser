<?php
    namespace modules\hoduser\aspect;
    use modules\hodaspect\lib\hodaspect\BaseAspect;

    class Authorize extends BaseAspect{
        function onMethodPreCall($parameters, $data)
        {

                if(!$this->auth->isAuthorized("attribute",$parameters[0],isset($parameters[1])?$parameters[1]:1)){
                    $this->event->raise("authorizationFail",array("parameters"=>$parameters,"data"=>$data));
                }


        }

    }

?>