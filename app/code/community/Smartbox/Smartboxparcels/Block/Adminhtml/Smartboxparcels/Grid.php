<?php

class Smartbox_Smartboxparcels_Block_Adminhtml_Smartboxparcels_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        
        parent::__construct();
        $this->setId('id');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        
        $collection = Mage::getModel('Smartbox_Smartboxparcels/smartboxparcels')->getCollection();
        //$collection->addAttributeToFilter('parcel_id', array('notnull' => true));
        $collection->getSelect()->join(
            array('sfo' => $collection->getTable('sales/order')),
            'sfo.entity_id=order_id'
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        
        $this->addColumn('id', array(
            'header'    => Mage::helper('Smartbox_Smartboxparcels')->__('ID'),
            'width'     => '10px',
            'index'     => 'id',
            'type'  => 'number'
        ));

        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('Smartbox_Smartboxparcels')->__('Order ID'),
            'width'     => '10px',
            'index'     => 'increment_id',
            'width'     => '10px',
        ));

        $this->addColumn('parcel_id', array(
            'header'    => Mage::helper('Smartbox_Smartboxparcels')->__('Parcel ID'),
            'width'     => '10px',
            'index'     => 'parcel_id',
            'width'     => '10px',
        ));

        $this->addColumn('tracking_number', array(
            'header'    => Mage::helper('Smartbox_Smartboxparcels')->__('Smartbox Tracking Number'),
            'width'     => '10px',
            'index'     => 'tracking_number',
            'width'     => '10px',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Order Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('parcel_status', array(
            'header'    =>  Mage::helper('Smartbox_Smartboxparcels')->__('Parcel Status'),
            'width'     =>  '10px',
            'index'     =>  'parcel_status',
            'type'      =>  'options',
            'options'   =>  Mage::helper('Smartbox_Smartboxparcels')->getParcelStatus(),
        ));

        $this->addColumn('parcel_target_machine_id', array(
            'header'    => 'Machine ID',
            'width'     => '10px',
            'index'     => 'parcel_target_machine_id',
            'width'     => '10px',
        ));

        $this->addColumn('creation_date', array(
            'header'    => Mage::helper('Smartbox_Smartboxparcels')->__('Creation date'),
            'width'     => '10px',
            'type'      => 'datetime',
            'index'     => 'creation_date',
            'gmtoffset' => true
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('Smartbox_Smartboxparcels')->__('Action'),
                'width'     => '10',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('Smartbox_Smartboxparcels')->__('Edit Parcel'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'id',
                'is_system' => true,
                'renderer'  => new Smartbox_Smartboxparcels_Block_Adminhtml_Smartboxparcels_Renderer_Link()
        ));

        //$this->addExportType('*/*/exportCsv', Mage::helper('inpostparcels')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('inpostparcels')->__('Excel XML'));
        //$this->addExportType('*/*/exportPdf', Mage::helper('inpostparcels')->__('PDF'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('parcels_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('parcels', array(
            'label'    => Mage::helper('Smartbox_Smartboxparcels')->__('Create multiple parcels'),
            'url'      => $this->getUrl('*/*/massCreateMultipleParcels')
        ));

        return $this;
    }



}
