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

namespace UOL\PagSeguro\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * Prepare database for install
         */
        $setup->startSetup();

        /**
         * PagSeguro Order Status
         */
        $statuses = [
            'pagseguro_iniciado'  => ['label' => __('PagSeguro Iniciado'), 'state' => 'new'],
            'pagseguro_aguardando_pagamento' => ['label' => __('PagSeguro Aguardando Pagamento'), 'state' => 'pending_payment'],
            'pagseguro_cancelada' => ['label' => __('PagSeguro Cancelada'), 'state' => 'canceled'],
            'pagseguro_chargeback_debitado'  => ['label' => __('PagSeguro Chargeback Debitado'), 'state' => 'closed'],
            'pagseguro_devolvida'  => ['label' => __('PagSeguro Devolvida'), 'state' => 'closed'],
            'pagseguro_disponivel'  => ['label' => __('PagSeguro Disponível'), 'state' => 'complete'],
            'pagseguro_em_analise'  => ['label' => __('PagSeguro Em Análise'), 'state' => 'payment_review'],
            'pagseguro_em_contestacao'  => ['label' => __('PagSeguro Em Contestação'), 'state' => 'holded'],
            'pagseguro_em_disputa'  => ['label' => __('PagSeguro Em Disputa'), 'state' => 'holded'],
            'pagseguro_paga'  => ['label' => __('PagSeguro Paga'), 'state' => 'complete'],
        ];

        foreach ($statuses as $code => $info) {
            $status[] = [
                'status' => $code,
                'label' => $info['label']
            ];
            $state[] = [
                'status' => $code,
                'state' => $info['state'],
                'is_default' => 0,
                'visible_on_front' => '1'
            ];
        }
        $setup->getConnection()
            ->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $status);

        /**
         * PagSeguro Order State
         */
        $state[0]['is_default'] = 1;
        $setup->getConnection()
            ->insertArray(
                $setup->getTable('sales_order_status_state'),
                ['status', 'state', 'is_default', 'visible_on_front'],
                $state
            );
        unset($data);

        /**
         * PagSeguro Store Reference
         */
        $data[] = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'pagseguro/store/reference',
            'value' => \UOL\PagSeguro\Helper\Data::generateStoreReference()
        ];
        $setup->getConnection()
            ->insertArray($setup->getTable('core_config_data'), ['scope', 'scope_id', 'path', 'value'], $data);

        /**
         * Prepare database after install
         */
        $setup->endSetup();
    }
}
