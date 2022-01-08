<?php

class CClientSideRequest
{
    private $build_query;
    private $build_success;

    public function __construct(string $mode, string $url, ?string $payload = null) 
    {
        $this->build_query = '
            "url" : "'.$url.'",
            "method" : "'.$mode.'",';
        if ($payload != null)
        {
            $this->build_query .= '
            "data" : '.$payload.',';
        }
        return $this;
    }

    public function addSuccess(?string $passthrough, ?string $endpoint) 
    {
        $this->build_success = '
            "success" : function (resData) {';
        if ($passthrough != null) 
        {
            $passthrough_req = new CClientSideRequest("POST", $passthrough, "resData");
            $passthrough_req->addSuccess(null, $endpoint);
            $this->build_success .= $passthrough_req->run(false);
            $this->build_success .= '},';
        }
        else if ($endpoint != null) 
        {
            $this->build_success .= '
                $("#'.$endpoint.'").html(resData);
                enable_edits();
                enable_select();
                enable_deletes();
                compute_dones();
                enable_mtms();
            },';
        }
        else {
            $this->build_success .= '},';
        }
    } 

    public function run(bool $with_script_tags = true) : string
    {
        $ret = "";
        if ($with_script_tags)
        {
            $ret .= '
            <script type="text/javascript">
            $(document).ready(function() {';
        }
        $ret .= '
            $.ajax({'.
                $this->build_query.
                $this->build_success.
            '});';
        if ($with_script_tags) 
        {
            $ret .= '
        }); 
        </script>';
        }
        return $ret;
    }
}

?>