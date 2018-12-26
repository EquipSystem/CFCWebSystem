<?php
/**
 * Cfcweb40CalendarForm Form
 * @author  <your name here>
 */
class Cfcweb40CalendarFormView extends TPage
{
    private $fc;

    protected $form;
    private $formFields = [];
    private static $database = 'cfcwebsystem';
    private static $activeRecord = 'Cfcweb40';
    private static $primaryKey = 'id';
    private static $formName = 'form_Cfcweb40';
    private static $startDateField = 'HORARIO_INICIAL';
    private static $endDateField = 'HORARIO_FINAL';
    

    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);
        // define the form title
        $this->form->setFormTitle('Marcação Aulas PD');

        $view = new THidden('view');
        
        $criteria_COD_INSTRUTOR = new TCriteria();
        $criteria_COD_INSTRUTOR->setProperty('order', 'CODIGO asc');

        $COD_INSTRUTOR = new TDBSeekButton('COD_INSTRUTOR', 'cfcwebsystem', self::$formName, 'Cfcweb05', 'NOME', 'COD_INSTRUTOR', 'COD_INSTRUTOR_display', $criteria_COD_INSTRUTOR);
        $COD_INSTRUTOR_display = new TEntry('COD_INSTRUTOR_display');
        $COD_INSTRUTOR->setDisplayMask('{NOME}');
        $COD_INSTRUTOR->setAuxiliar($COD_INSTRUTOR_display);
        $COD_INSTRUTOR_display->setEditable(false);
        $COD_INSTRUTOR->setSize(70);
        $COD_INSTRUTOR_display->setSize(650);

        $this->form->appendPage('Dados Instrutor');
        $row1 = $this->form->addFields([new TLabel('CÓD.INSTRUTOR:', '#ff0000', '14px', null)],[$COD_INSTRUTOR]);

		
        $this->fc = new TFullCalendar(date('Y-m-d'), 'month');
        $this->fc->enableDays([0,1,2,3,4,5,6]);
        $this->fc->setReloadAction(new TAction(array($this, 'getEvents')));
        $this->fc->setDayClickAction(new TAction(array('Cfcweb40CalendarForm', 'onStartEdit')));
        $this->fc->setEventClickAction(new TAction(array('Cfcweb40CalendarForm', 'onEdit')));
        $this->fc->setEventUpdateAction(new TAction(array('Cfcweb40CalendarForm', 'onUpdateEvent')));
        $this->fc->setCurrentView('agendaWeek');
        $this->fc->setTimeRange('00:00', '23:00');
        $this->fc->enablePopover('Mais Informações', " <b>Evento</b>:  {EVENTO}  <br>

<b>Data Horário Inicial</b>: {HORARIO_INICIAL}  <b> Data Horário Final</b>: {HORARIO_FINAL}  <br>

<b>Instrutor</b>:  {fk_COD_INSTRUTOR->CODIGO}  {fk_COD_INSTRUTOR->NOME}  <br>

<b>Veículo</b>: {fk_COD_VEICULO->CODIGO}  {fk_COD_VEICULO->DESCRICAO}  <br>

<b>Aluno</b>: {fk_COD_ALUNO->CODIGO}  {fk_COD_ALUNO->NOME} <br>");

        $this->form->addFields([$view]);
        $btn_onsearch = $this->form->addAction('Buscar', new TAction([$this, 'getEvents']), 'fa:search #333333');
        parent::add( $this->form );

        parent::add( $this->fc );
    }

    /**
     * Output events as an json
     */
    public static function getEvents($param=NULL)
    {
        $return = array();
        try
        {
            TTransaction::open('cfcwebsystem');

            $criteria = new TCriteria(); 

            $criteria->add(new TFilter('HORARIO_INICIAL', '>=', $param['start'].' 00:00:00'));
            $criteria->add(new TFilter('HORARIO_FINAL', '<=', $param['end'].' 23:59:59'));

            $filterVar = ($param['COD_INSTRUTOR']); //self::$fc(COD_INSTRUTOR);
            
            $criteria->add(new TFilter('COD_INSTRUTOR', '=', $filterVar)); 

            $events = Cfcweb40::getObjects($criteria);

            if ($events)
            {
                foreach ($events as $event)
                {
                    $aluno = $event->COD_ALUNO; // ALTERADO AQUI
                    $event_array = $event->toArray();
                    $event_array['start'] = str_replace( ' ', 'T', $event_array['HORARIO_INICIAL']);
                    $event_array['end'] = str_replace( ' ', 'T', $event_array['HORARIO_FINAL']);
                    $event_array['color'] = $event_array['COR'];
                    $event_array['title'] = TFullCalendar::renderPopover($event_array['EVENTO'], $event->render("Mais Informações"), $event->render(" <b>Evento</b>:  {EVENTO}  <br>

<b>Data Horário Inicial</b>: {HORARIO_INICIAL}  <b> Data Horário Final</b>: {HORARIO_FINAL}  <br>

<b>Instrutor</b>:  {fk_COD_INSTRUTOR->CODIGO}  {fk_COD_INSTRUTOR->NOME}  <br>

<b>Veículo</b>: {fk_COD_VEICULO->CODIGO}  {fk_COD_VEICULO->DESCRICAO}  <br>

<b>Aluno</b>: {fk_COD_ALUNO->CODIGO}  {fk_COD_ALUNO->NOME} <br>"));
                    //$event_array['title'] = $event_array['titulo']. "<br>Aluno: {$aluno->COD_ALUNO}"; // ALTERARADO AQUI

                    $return[] = $event_array;
                }
            }
            TTransaction::close();
            echo json_encode($return);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Reconfigure the callendar
     */
    public function onReload($param = null)
    {
        if (isset($param['view']))
        {
            $this->fc->setCurrentView($param['view']);
        }

        if (isset($param['date']))
        {
            $this->fc->setCurrentDate($param['date']);
        }
    }

    public function onFiltros($param = null)
    {
    }


}

