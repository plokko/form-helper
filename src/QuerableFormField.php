<?php


namespace Plokko\FormHelper;


use \UnexpectedValueException;

class QuerableFormField extends FormField
{
    protected
        $table=null,
        $model=null,
        $field=null,

        $query,
        $limit=6;


    function __construct(FormHelper $parent, $name)
    {
        parent::__construct($parent, $name);
    }

    /**
     * @param Closure $query
     */
    function query($query){
        $this->query = $query;
        return $this;
    }

    function getModel(){
        return $this->model?new $this->model():null;
    }

    function getTable(){
        return $this->table?
                    $this->table:
                    optional($this->getModel())->getTable();
    }


    function table($table,$field){
        $this->table = $table;
        $this->field = $field;
        return $this;
    }
    function model($model,$field){
        $this->model = $model;
        $this->field = $field;
        return $this;
    }

    /**
     * Limit the number fetched of items
     * @param int $n
     * @return $this
     */
    function limit($n=6){
        $this->limit = $n;
        return $this;
    }

    /**
     * Execute search query
     * @internal
     * @param $search
     * @return mixed
     */
    function _executeSearchQuery($search){
        $query = $this->query;
        if(!$query) {
            if(!$this->field)
                throw new UnexpectedValueException('QuerableFormField('.$this->name.') error: field is not set');
            $query = function($search){
                $q = $this->getModel();
                if(!$q){
                    if(!$this->table)
                        throw new UnexpectedValueException('QuerableFormField('.$this->name.') error: table or model not set!');
                    $q = \DB::table($this->table);
                }
                return $q->where($this->field,'like','%'.trim($search).'%');
            };
        }
        $q = $query($search);

        return $q->take($this->limit)->get();
    }
}
