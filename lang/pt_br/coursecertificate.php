<?php
// This file is part of the mod_coursecertificate plugin for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_coursecertificate
 * @category    string
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activityhiddenwarning'] ='Esta atividade está atualmente oculta. Ao torná-lo visível, os alunos que atenderem às restrições de acesso às atividades receberão automaticamente uma cópia em PDF do certificado.';
$string['archivecertificates'] = 'Arquivar certificados emitidos';
$string['archivecertificates_help'] = 'Os certificados arquivados ainda podem ser verificados e ainda são exibidos na página de perfil do usuário. No entanto, quando um certificado de curso existente é arquivado, um usuário pode receber um novo certificado assim que satisfizer as restrições de acesso à atividade.';
$string['automaticsend_helptitle'] = "Ajuda com envio automático";
$string['automaticsenddisabled'] = 'O envio automático deste certificado está desabilitado.';
$string['automaticsenddisabled_help'] = 'Ao deixar desabilitado, o aluno deverá clicar no link da atividade exibido na página do curso para receber o certificado, desde que atenda às restrições de acesso a esta atividade.<br/><br/>
Ao habilitá-lo, os alunos receberão automaticamente uma cópia em PDF do certificado assim que atenderem às restrições de acesso desta atividade. Observe que todos os alunos que já atenderem às restrições de acesso a esta atividade receberão o certificado ao habilitá-la.';
$string['automaticsenddisabledalert'] = 'Os alunos que atenderem às restrições de acesso a esta atividade receberão seu certificado assim que acessarem.';
$string['automaticsenddisabledinfo'] ='Atualmente, os alunos {$a} atendem às restrições de acesso a esta atividade e receberão seu certificado assim que acessá-la.';
$string['automaticsendenabled'] = 'O envio automático deste certificado está habilitado.';
$string['automaticsendenabled_help'] = 'Ao deixar isso ativado, os alunos receberão automaticamente uma cópia em PDF do certificado assim que atenderem às restrições de acesso desta atividade.<br/><br/>
Ao desativá-lo, os alunos precisarão clicar no link da atividade exibido na página do curso para receber o certificado, uma vez que atendam às restrições de acesso a essa atividade.';
$string['certificateissues'] = 'Emissões de certificados';
$string['certificatesarchived'] = 'Certificados arquivados';
$string['certifiedusers'] = 'Usuários certificados';
$string['chooseatemplate'] = 'Escolha um modelo...';
$string['code'] = 'Código';
$string['coursecertificate:addinstance'] = 'Adicionar uma nova atividade de certificado de Curso';
$string['coursecertificate:receive'] = 'Receber certificados emitidos';
$string['coursecertificate:view'] = 'Ver certificado do Curso';
$string['coursecertificate:viewreport'] = 'Visualizar relatório de emissão de certificados do curso';
$string['coursecompletiondate'] = 'Data de conclusão';
$string['courseinternalid'] = 'ID do curso interno usado em URLs';
$string['courseurl'] = 'URL do curso';
$string['disableautomaticsend'] = 'Os alunos não receberão mais automaticamente uma cópia em PDF do certificado assim que se encontrarem
as restrições de acesso desta atividade. Em vez disso, eles precisarão clicar no link da atividade exibido na página do curso para receber
  o certificado, uma vez que atendam às restrições de acesso desta atividade.';
$string['enableautomaticsend'] = 'Todos os alunos receberão automaticamente uma cópia em PDF do certificado assim que atenderem às restrições de acesso desta atividade.<br/><br/>
Atualmente, {$a} alunos já atendem a essas restrições de acesso, mas ainda não acessaram esta atividade. Eles também receberão imediatamente sua cópia.<br/><br/>
Os alunos que já acessaram esta atividade não receberão o certificado novamente.';
$string['enableautomaticsendpopup'] = 'Todos os alunos receberão automaticamente uma cópia em PDF do certificado assim que atenderem às restrições de acesso desta atividade.<br/><br/>
Os alunos que já atendem a essas restrições de acesso, mas ainda não acessaram esta atividade, também receberão imediatamente sua cópia.<br/><br/>,
Os alunos que já acessaram esta atividade não receberão o certificado novamente.';
$string['expirydate'] = 'Data de validade';
$string['issueddate'] = 'Data de emissão';
$string['managetemplates'] = 'Gerenciar modelos de certificado';
$string['modulename'] = 'Certificado do curso';
$string['modulename_help'] = 'O módulo de certificado do curso oferece uma oportunidade para os alunos celebrarem as conquistas
  obtenção de certificados.<br/><br/> Permite escolher entre diferentes modelos de certificados que exibirão automaticamente os dados do usuário,
  como nome completo, curso, etc. <br/><br/> Os próprios usuários poderão baixar uma cópia em PDF do certificado acessando este",
  atividade, e há opções para enviar uma cópia em PDF para eles por e-mail automaticamente.<br/><br/>Se o modelo usado nesta atividade contiver
  um código QR, os usuários poderão digitalizá-lo para validar seus certificados.';
$string['modulename_link'] = 'mod/certificado/view';
$string['modulenameplural'] = 'Certificados de curso';
$string['notemplateselected'] = 'O modelo selecionado não pode ser encontrado. Vá para as configurações de atividade e selecione uma nova.';
$string['notemplateselecteduser'] = 'O certificado não está disponível. Entre em contato com o administrador do curso.';
$string['notemplateswarning'] = 'Não há modelos disponíveis. Entre em contato com o administrador do site.';
$string['notemplateswarningwithlink'] = 'Não há modelos disponíveis. Por favor, vá para <a href",{$a}>certificate template management page</a> and create a new one.';
$string['nouserscertified'] = 'Nenhum usuário é certificado.';
$string['open'] = 'Abrir';
$string['page-mod-coursecertificate-x'] = 'Qualquer página de módulo de certificado de curso';
$string['pluginadministration'] = 'Administração de certificado de curso';
$string['pluginname'] = 'Certificado do curso';
$string['previewcoursefullname'] = 'Nome completo do curso';
$string['previewcourseshortname'] = 'Nome abreviado do curso';
$string['privacy:metadata'] = 'A atividade de certificado de curso não armazena dados pessoais.';
$string['revoke'] = 'Revogar';
$string['revokeissue'] = 'Tem certeza que deseja revogar esta emissão de certificado deste usuário?';
$string['selectdate'] = 'Selecione a data';
$string['selecttemplatewarning'] = 'Assim que esta atividade emitir pelo menos um certificado, este campo ficará bloqueado e não poderá mais ser editado.';
$string['status'] = 'Status';
$string['taskissuecertificates'] = 'Emitir certificados de curso';
$string['template'] = 'Modelo';
