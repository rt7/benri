<?php
/*
 * douggr/zf-rest
 *
 * @link https://github.com/douggr/zf-rest for the canonical source repository
 * @version 2.0.0
 *
 * For the full copyright and license information, please view the LICENSE
 * file distributed with this source code.
 */

/**
 * {@inheritdoc}
 */
class ZfRest_Controller_Rest extends ZfRest_Controller_Action_Abstract
{
    /**
     * Request data
     */
    protected $_input;

    /**
     * Response data
     */
    private $_data;

    /**
     * @var array
     */
    private $_errors = [];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->_registerPlugin(new ZfRest_Controller_Plugin_CORS());
        $this->_registerPlugin(new Zend_Controller_Plugin_PutHandler());

        $this->_helper
            ->layout()
            ->disableLayout();

        $this->_helper
            ->viewRenderer
            ->setNoRender(true);

        $this->_input   = new StdClass();
        $this->_data    = [
            'messages'  => [],
            'data'      => null
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function postDispatch()
    {
        $this->_data['messages'] = $this->_messages;

        if (count($this->_errors)) {
            $this->_data['errors'] = $this->_errors;
        }

        $pretty = $this->getRequest()
            ->getParam('pretty');

        if (null !== $pretty) {
            $jsonOptions = JSON_NUMERIC_CHECK | JSON_HEX_AMP | JSON_PRETTY_PRINT;
        } else {
            $jsonOptions = JSON_NUMERIC_CHECK | JSON_HEX_AMP;
        }

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setBody(json_encode($this->_data, $jsonOptions));
    }

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $error   = null;
        $request = $this->getRequest();

        // we don't need this good guy no anymore…
        unset($_POST);

        if (!$request->isGet() && !$request->isHead()) {
            // … we read data from the request body.
            $this->_input   = json_decode(file_get_contents('php://input'));
            $jsonError      = json_last_error();

            if (JSON_ERROR_NONE !== $jsonError) {
                switch ($jsonError) {
                    case JSON_ERROR_DEPTH:
                        $error = "Problems parsing JSON data.\nThe maximum stack depth has been exceeded.";
                        break;

                    case JSON_ERROR_STATE_MISMATCH:
                        $error = "Invalid or malformed JSON.";
                        break;

                    case JSON_ERROR_CTRL_CHAR:
                        $error = "Problems parsing JSON data.\nControl character error, possibly incorrectly encoded.";
                        break;

                    case JSON_ERROR_SYNTAX:
                        $error = "Syntax error, malformed JSON.";
                        break;

                    case JSON_ERROR_UTF8:
                        $error = "Problems parsing JSON data.\nMalformed UTF-8 characters, possibly incorrectly encoded.";
                        break;

                    case JSON_ERROR_RECURSION:
                        $error = "Problems parsing JSON data.\nOne or more recursive references in the value to be encoded.";
                        break;

                    case JSON_ERROR_INF_OR_NAN:
                        $error = "Problems parsing JSON data.\nOne or more NAN or INF values in the value to be encoded.";
                        break;

                    case JSON_ERROR_UNSUPPORTED_TYPE:
                        $error = "Problems parsing JSON data.\nA value of a type that cannot be encoded was given.";
                        break;
                }

                $this->getResponse()
                    ->setHttpResponseCode(403)
                    ->setHeader('Content-Type', 'text/plain; charset=utf-8')
                    ->setBody($error)
                    ->sendResponse();

                exit -403;
            }
        }
    }

    /**
     * All error objects have field and code properties so that your client
     * can tell what the problem is.
     *
     * If resources have custom validation errors, they should be documented
     * with the resource.
     *
     * @param string $field The erroneous field or column
     * @param string $code One of the ERROR_* codes contants
     * @param string $message
     * @param array $interpolateParams Params to interpolate within the message
     * @return ZfRest_Db_Table_Abstract_Row
     */
    protected function _pushError($resource, $field, $title)
    {
        $this->getResponse()
            ->setHttpResponseCode(422);

        $this->_errors[] = [
            'field'     => $field,
            'resource'  => $resource,
            'title'     => $title
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _setResponseData($data)
    {
        $this->_data['data'] = $data;
    }
}
