<?php

class Smartbox_Smartboxparcels_Block_Adminhtml_System_Config_Smartbox_Moduleversion
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $useContainerId = $element->getData('use_container_id');
        return sprintf('<tr id="row_%s">
                <td class="label">
                    <strong id="%s">%s</strong>
                </td>
                <td class="value">
                    <span id="module-version">%s</span>
                </td>
            </tr>',
            $element->getHtmlId(), $element->getHtmlId(), $element->getLabel(), $this->getVersionHtml()
        );
    }

    private function getVersionHtml()
    {
        $response = Mage::getConfig()->getModuleConfig('Smartbox_Smartboxparcels')->version;
        return $response;
    }
}
