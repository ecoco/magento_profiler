<?php

/**
 * Class Ecocode_Profiler_Block_Renderer_Settings
 *
 */
class Ecocode_Profiler_Block_Renderer_Settings_Field
    extends Ecocode_Profiler_Block_Renderer_AbstractRenderer
{
    protected $templateFiles = [
        'base'   => 'ecocode_profiler/renderer/settings/input.phtml',
        'select' => 'ecocode_profiler/renderer/settings/select.phtml'
    ];

    protected $fieldAttributes = [
        'base'   => ['id', 'type', 'class', 'name', 'value'],
        'text'   => ['length'],
        'number' => ['min', 'max']
    ];


    public function renderField($type, $name, $value, array $data = [])
    {
        $data['type']  = $type;
        $data['name']  = $name;
        $data['value'] = $value;

        return $this->render($data);
    }

    /**
     * @param array $data
     * @return string
     */
    public function render(array $data = [])
    {
        $type = isset($data['type']) ? $data['type'] : 'base';
        if (!isset($data['template'])) {
            if (!isset($this->templateFiles[$type])) {
                $data['template'] = $this->templateFiles['base'];
            } else {
                $data['template'] = $this->templateFiles[$type];
            }
        }
        $this->setTemplate($data['template']);

        $data['attributes'] = $this->prepareAttributes($type, $data);

        return parent::render($data);
    }

    protected function prepareAttributes($type, array $data)
    {
        $fieldAttributeKeys = isset($this->fieldAttributes[$type]) ? $this->fieldAttributes[$type] : [];
        $fieldAttributeKeys = array_merge($this->fieldAttributes['base'], $fieldAttributeKeys);
        $attributes         = array_intersect_key($data, array_fill_keys($fieldAttributeKeys, 1));

        if (isset($data['attributes'])) {
            $attributes = array_merge($attributes, $data['attributes']);
        }

        if (isset($data['data'])) {
            $dataAttributeKeys = array_keys($data['data']);
            //prepend with 'data-'
            $dataAttributeKeys = array_map(function ($k) {
                return 'data-' . $k;
            }, $dataAttributeKeys);

            $dataAttributes = array_combine($dataAttributeKeys, $data['data']);
            $attributes     = array_merge($attributes, $dataAttributes);
        }

        array_walk($attributes, function (&$value, $key) {
            $value = sprintf('%s="%s"', $key, $this->escapeHtml($value));
        });

        return $attributes;
    }
}
