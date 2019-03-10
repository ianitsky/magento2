<?php
/**
 * 2007-2016 [PagSeguro Internet Ltda.]
 *
 * NOTICE OF LICENSE
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *you may not use this file except in compliance with the License.
 *You may obtain a copy of the License at
 *
 *http://www.apache.org/licenses/LICENSE-2.0
 *
 *Unless required by applicable law or agreed to in writing, software
 *distributed under the License is distributed on an "AS IS" BASIS,
 *WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *See the License for the specific language governing permissions and
 *limitations under the License.
 *
 *  @author    PagSeguro Internet Ltda.
 *  @copyright 2016 PagSeguro Internet Ltda.
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

namespace UOL\PagSeguro\Model\Transactions\Methods;

use UOL\PagSeguro\Model\Transactions\Method;

/**
 * Class Transactions
 *
 * @package UOL\PagSeguro\Model\Transactions
 */
class Transactions extends Method
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var integer
     */
    protected $_idMagento;

    /**
     * @var array
     */
    protected $_arrayTransactions = array();

    /**
     * @var \PagSeguro\Parsers\Transaction\Search\Date\Response
     */
    protected $_PagSeguroPaymentList;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Grid
     */
    protected $_salesGrid;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \UOL\PagSeguro\Helper\Library
     */
    protected $_library;

    /**
     * @var \UOL\PagSeguro\Helper\Crypt
     */
    protected $_crypt;

    /**
     * @var array
     */
    protected $_detailsTransactionByCode;

    /**
     * @var bool
     */
    protected $_needConciliate = true;

    /** Url Path */
    const URL_PATH_MAGENTO_ORDER = 'sales/order/view';

    /**
     * Conciliation constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Sales\Model\Order $order
     * @param \UOL\PagSeguro\Helper\Library $library
     * @param $days
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\ObjectManager\ContextInterface $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Backend\Model\Session $session,
        \Magento\Sales\Model\Order $order,
        \UOL\PagSeguro\Helper\Library $library,
        \UOL\PagSeguro\Helper\Crypt $crypt
    ) {
        /** @var  \Magento\Framework\UrlInterface context */
        $this->urlBuilder = $urlBuilder;
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface _scopeConfig */
        $this->_scopeConfig = $scopeConfigInterface;
        /** @var  \Magento\Framework\App\ResourceConnection _resource */
        $this->_resource = $resourceConnection;
        /** @var  \Magento\Backend\Model\Session  _session */
        $this->_session = $session;
        /** @var \Magento\Sales\Model\Order _order */
        $this->_order = $order;
        /** @var \UOL\PagSeguro\Helper\Library _library */
        $this->_library = $library;
        /** @var \UOL\PagSeguro\Helper\Crypt _crypt */
        $this->_crypt = $crypt;
        /** @var int _idMagento */
        $this->_idMagento = $request->getParam('id_magento');
        /** @var int _idPagseguro */
        $this->_idPagseguro = $request->getParam('id_pagseguro');
        /** @var int _dateBegin */
        $this->_dateBegin = $request->getParam('date_begin');
        /** @var int _dateEnd */
        $this->_dateEnd = $request->getParam('date_end');
        /** @var int _status */
        $this->_status = $request->getParam('status');

        /** @var \Magento\Sales\Model\ResourceModel\Grid _salesGrid */
        $this->_salesGrid = new \Magento\Sales\Model\ResourceModel\Grid(
            $context,
            'pagseguro_orders',
            'sales_order_grid',
            'order_id'
        );
    }

    /**
     * Get all transactions and return formatted data
     *
     * @return array
     * @throws \Exception
     */
    public function request()
    {
        $transactions = $this->searchTransactions();

        if(count($transactions) > 0) {
            foreach ($transactions as $transaction) {
                $this->_arrayTransactions[] = array(
                    'date'           => $this->formatDate($transaction['created_at']),
                    'magento_id'     => $transaction['increment_id'],
                    'pagseguro_id'   => $transaction['transaction_code'],
                    'environment'    => $transaction['environment'],
                    'magento_status' => $this->formatMagentoStatus($transaction['status'], $transaction['partially_refunded']),
                    'order_id'       => $transaction['entity_id'],
                    'order_link'     => $this->urlBuilder->getUrl(self::URL_PATH_MAGENTO_ORDER, ['order_id' => $transaction['entity_id']])
                );
            }
        }
        return $this->_arrayTransactions;
    }

    /**
     * Get details transactions
     *
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function execute($data) {

        $this->getDetailsTransaction($data);

        if(!empty($this->_detailsTransactionByCode) && $this->_needConciliate){
            throw new \Exception('need to conciliate');
        }

        if (empty($this->_detailsTransactionByCode)) {
            throw new \Exception('empty');
        }
        return $this->_detailsTransactionByCode;
    }


    /**
     * Build data for dataTable
     *
     * @param $payment
     * @param $order
     * @return array
     */
    protected function build($payment, $order)
    {
        throw new NotImplementedException();
    }

    /**
     * Get data for details
     *
     * @param $order
     * @param $payment
     * @param $options
     * @return string
     */
    protected function details($order, $payment, $options)
    {
        throw new NotImplementedException();
    }

}
