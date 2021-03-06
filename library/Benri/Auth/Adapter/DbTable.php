<?php

/**
 * Provides the ability to authenticate against credentials stored in a database
 * table.
 *
 * @link http://framework.zend.com/manual/1.12/en/zend.auth.adapter.dbtable.html Zend_Auth_Adapter_DbTable
 * @link http://framework.zend.com/manual/1.12/en/zend.auth.introduction.html#zend.auth.introduction.results Zend_Auth_Result
 */
class Benri_Auth_Adapter_DbTable extends Zend_Auth_Adapter_DbTable
{
    /**
     * The table name to check.
     *
     * @var string
     */
    protected $_tableName = 'user';

    /**
     * The column to use as the identity.
     *
     * @var string
     */
    protected $_identityColumn = 'username';

    /**
     * Columns to be used as the credentials.
     *
     * @var string
     */
    protected $_credentialColumn = 'password';

    /**
     * Sets a flag for usage of identical identities with unique credentials.
     * It accepts ints (0, 1) or bool (true, false) parameters.
     *
     * @internal
     * @param int $flag
     * @return Benri_Auth_Adapter_DbTable
     */
    public function setAmbiguityIdentity($flag)
    {
        $this->_ambiguityIdentity = false;
    }

    /**
     * Called to attempt an authentication.
     *
     * Previous to this call, this adapter would have already been configured
     * with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @return Zend_Auth_Result
     * @see http://framework.zend.com/manual/1.12/en/zend.auth.introduction.html#zend.auth.introduction.results Zend_Auth_Result
     * @throws Zend_Auth_Adapter_Exception if answering the authentication
     *  query is impossible
     */
    public function authenticate()
    {
        $this->_authenticateSetup();

        $dbSelect   = $this->_authenticateCreateSelect();
        $identity   = $this->_authenticateQuerySelect($dbSelect);
        $authResult = $this->_authenticateValidateResultSet($identity);

        if ($authResult instanceof Zend_Auth_Result) {
            return $authResult;
        }

        /// _authenticateValidateResult() attempts to make certain that only
        /// one record was returned in the resultset.
        return $this->_authenticateValidateResult(array_shift($identity));
    }

    /**
     * Creates a Zend_Db_Select object that is completely configured to be
     * queried against the database.
     *
     * @return Zend_Db_Select
     * @see http://framework.zend.com/manual/1.12/en/zend.db.select.html Zend_Db_Select
     */
    protected function _authenticateCreateSelect()
    {
        $dbSelect = clone $this->getDbSelect();
        $dbSelect->from($this->_tableName)
            ->where("{$this->_identityColumn} = ?", $this->_identity)
            ->limit(1);

        return $dbSelect;
    }

    /**
     * Attempts to make certain that only one record was returned in the
     * resultset.
     *
     * @param array $resultIdentities
     * @return Zend_Auth_Result
     * @see http://framework.zend.com/manual/1.12/en/zend.auth.introduction.html#zend.auth.introduction.results Zend_Auth_Result
     */
    protected function _authenticateValidateResult($resultIdentity)
    {
        $code    = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
        $message = 'Supplied credential is invalid.';

        if (Benri_Util_String::verifyPassword($this->_credential, $resultIdentity[$this->_credentialColumn])) {
            $code             = Zend_Auth_Result::SUCCESS;
            $message          = 'Authentication successful.';
            $this->_resultRow = $resultIdentity;
        }

        $this->_authenticateResultInfo['code']       = $code;
        $this->_authenticateResultInfo['messages'][] = $message;

        /// _authenticateCreateAuthResult creates a Zend_Auth_Result object
        /// from the information that has been collected during the
        /// Benri_Auth_Adapter_DbTable::authenticate() attempt.
        return $this->_authenticateCreateAuthResult();
    }
}
