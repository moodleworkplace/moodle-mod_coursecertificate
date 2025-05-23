<?php
namespace mod_coursecertificate\form;

defined('MOODLE_INTERNAL') || die;


use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;

require_once($CFG->dirroot . '/lib/externallib.php');

class form_template_options extends \external_api {

    /**
     * Função que será chamada externamente para buscar as opções de templates.
     *
     * @param string $search O termo de busca.
     * @param array $options Parâmetros adicionais.
     * @return array
     */
    public static function get_options($search) {
        global $COURSE;

        // Obter o contexto do curso
        $context = \context_course::instance($COURSE->id);
        $result = [];

        // Buscar templates de certificados visíveis
        $templates = \tool_certificate\permission::get_visible_templates($context);

        // Filtrar os templates com base no termo de busca
        $count = 0;
        foreach ($templates as $template) {
            $name = format_string($template->name, true, ['context' => $context]);
            if (stripos($name, $search) !== false) {
                $result[] = [
                    'value' => $template->id,
                    'label' => $name,
                ];
                $count++;
                if ($count >= 10) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Define os parâmetros esperados pela função externa.
     *
     * @return external_function_parameters
     */
    public static function get_options_parameters() {
        return new external_function_parameters([
            'search' => new external_value(PARAM_RAW, 'Termo de busca', VALUE_DEFAULT,null),
        ]);
    }

    /**
     * Define o tipo de retorno da função externa.
     *
     * @return external_multiple_structure
     */
    public static function get_options_returns() {
        return new external_multiple_structure(new external_single_structure([
            'value' => new external_value(PARAM_INT, 'Id of template'),
            'label' => new external_value(PARAM_RAW, 'The name of template'),
        ]));
    }

}
