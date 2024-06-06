<?php 

namespace App\Utils;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaginationableUtil
{
    /**
     * Model Related for pagination
     *
     * @var Model
     */
    protected $model;

    /**
     * Request that get from Client
     *
     * @var Request
     */
    protected $request;

    /**
     * Default Per Page Fetched Data
     *
     * @var integer
     */
    protected $per_page = 15;

    /**
     * Default params name for searching
     *
     * @var string
     */
    protected $default_search_param_key = "q";

    /**
     * Searchable columns from related Model
     *
     * @var array $searchable_cols
     */
    protected $searchable_cols = [];

    /**
     * With Relations
     *
     * @var array
     */
    protected $with_relations = [];

    /**
     * Filterable Cols
     *
     * @var array
     */
    protected $filterable_cols = [];
    
    /**
     * Available Cols
     *
     * @var array
     */
    protected $sortby_cols = [ "created_at" ];

    /**
     * Pagination Order Column (default is created_at)
     *
     * @var string
     */
    protected $pagination_order_col = "created_at";

    /**
     * Pagination Order By (default is desc)
     *
     * @var string
     */
    protected $pagination_order_by = "desc";

    /**
     * Transform response to "Laravel Resource" collection
     *
     * @var null|string|JsonResource
     */
    protected $transformToResourceClass = null;

    /**
     * Create Paginate by Current Model
     *
     * @return LengthAwarePaginator
     */
    public function paginate()
    {
        $this->setBulkSearch()
            ->initWithRelations()
            ->initWithFilters()
            ->setOrderBy();

        $data = $this->model->paginate($this->per_page)->withQueryString();

        if ($this->transformToResourceClass) {
            $data->getCollection()->transform(fn($item) => $this->transformToResourceClass::make($item));
        }

        return $data;
    }

    public function initModel()
    {
        return $this->setBulkSearch()
                ->initWithRelations()
                ->initWithFilters()
                ->setOrderBy();
    }

    /**
     * Get Current Model
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Model|Builder|QueryBuilder $model
     * @return self
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @param string $reasourceClass
     * @return self
     */
    public function setTransformResourceClass($reasourceClass)
    {
        $this->transformToResourceClass= $reasourceClass;
        return $this;
    }

    /**
     * Set Request
     *
     * @param Request $request
     * @return self
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        // Init By Request
        $this->per_page             = $this->request->input("per_page", $this->per_page);
        $this->pagination_order_col = $this->request->input("sort_by", $this->pagination_order_col);
        $this->pagination_order_by  = $this->request->input("order_by", $this->pagination_order_by);

        return $this;
    }

    public function setDateRange($dateFromRequest, $dateColName)
    {
        // Date Format from datepicker flatpickr
        $explodedDate = explode(' - ', $dateFromRequest);

        if (count($explodedDate) === 2)
        {
            $this->model = $this->model->whereBetween($dateColName, $explodedDate);
        }
        else if (count($explodedDate) === 1)
        {
            $this->model = $this->model->whereDate($dateColName, $explodedDate[0]);
        }

        return $this;
    }

    public function setWithTrashed()
    {
        $this->model = $this->model->withTrashed();

        return $this;
    }

    /**
     * Set Order by column name
     *
     * @param string $col_name
     * @return self
     */
    public function setPaginationOrderCol(string $col_name)
    {
        $this->pagination_order_col = $col_name;
        return $this;
    }

    public function setFilterableCols(array $value)
    {
        $this->filterable_cols = $value;
        return $this;
    }

    public function setSortByCols(array $value)
    {
        $this->sortby_cols = $value;
        return $this;
    }

    /**
     * Set Order By Ascending/Descending
     *
     * @param string $order_by (asc or desc)
     * @return self
     */
    public function setPaginationOrderBy(string $order_by)
    {
        $this->pagination_order_by = $order_by;
        return $this;
    }

    /**
     * Set searchable_cols for searching through columns
     *
     * @param array $searchable_cols
     * @return self
     */
    public function setSearchableColumns(array $searchable_cols = [])
    {
        $this->searchable_cols = $searchable_cols;
        return $this;
    }

    /**
     * Set Item Per Page
     *
     * @param integer $value
     * @return self
     */
    public function setPerPage(int $value)
    {
        $this->per_page = $value;
        return $this;
    }

    /**
     * Set With relations
     *
     * @param array $withs
     * @return self
     */
    public function setWithRelations(array $withs)
    {
        $this->with_relations = $withs;
        return $this;
    }

    /**
     * Init Model Relations (if exist)
     *
     * @return self
     */
    public function initWithRelations()
    {
        if (!empty($this->with_relations)) {
            $this->model = $this->model->with($this->with_relations);
        }

        return $this;
    }

    public function initWithFilters()
    {
        foreach ($this->filterable_cols as $col_name) {
            if ($this->request->filled($col_name)) {
                $this->model = $this->model->where($col_name, $this->request->{$col_name});
            }
        }

        return $this;
    }

    /**
     * Search through multiple columns that defined from searchable_cols
     *
     * @return self
     */
    public function setBulkSearch()
    {
        $search_text = $this->request->{$this->default_search_param_key};

        // If The request query not found
        if (!$search_text) {
            return $this;
        }

        $this->model = $this->model->where( function($query) use($search_text) {
            foreach ($this->searchable_cols as $col) {
                $query = $this->_searchQueryBySearchableCol($col, $search_text, $query);
                // $query = $query->orWhere($col, 'ILIKE', '%'.$search_text.'%');
            }
        });

        return $this;
    }

    /**
     * Search by searchable Col
     *
     * @param string $searchable_col
     * @param string $search_text
     * @param QueryBuilder|Builder $query
     * @return QueryBuilder|Builder
     */
    private function _searchQueryBySearchableCol($searchable_col, $search_text, $query)
    {
        $exploded_relations = explode(".", $searchable_col);

        if (count($exploded_relations) === 1) 
        {
            // $query = $query->orWhere($searchable_col, 'ILIKE', '%'.$search_text.'%');
            
            $search_text = Str::lower($search_text);
            $query = $query->orWhere( DB::raw("lower({$searchable_col})"), 'LIKE', '%'.$search_text.'%');
        }
        else if (count($exploded_relations) > 1)
        {
            $col = array_pop($exploded_relations);
            $query = $this->_recursiveQuerySearch($query, $col, $search_text, $exploded_relations);
        }

        return $query;
    }

    /**
     * Recursively using whereHas so it can search through relations
     *
     * @param QueryBuilder|Builder $query
     * @param string $searchable_col
     * @param string $search_text
     * @param array $relations
     * @return QueryBuilder|Builder
     */
    private function _recursiveQuerySearch($query, $searchable_col, $search_text, $relations)
    {
        if (empty($relations)) 
        {
            $cols = explode(",", $searchable_col);

            foreach ($cols as $col) {
                $query = $query->orWhere($col, 'ILIKE', '%'.$search_text.'%');
            }
        }
        else
        {
            $relation = array_shift($relations);

            if (Str::endswith($relation, 'able'))
            {
                $query = $query->whereHasMorph($relation, '*', function($q) use($searchable_col, $search_text, $relations) {
                    $q->where( function($queryTemp) use($searchable_col, $search_text, $relations) {
                        return $this->_recursiveQuerySearch($queryTemp, $searchable_col, $search_text, $relations);
                    });
                });
            }
            else 
            {
                $query = $query->whereHas($relation, function($q) use($searchable_col, $search_text, $relations) {
                    $q->where( function($queryTemp) use($searchable_col, $search_text, $relations) {
                        return $this->_recursiveQuerySearch($queryTemp, $searchable_col, $search_text, $relations);
                    });
                });
            }

        }

        return $query;
    }

    /**
     * Set ordering for pagination
     * 
     * @return self
     */
    public function setOrderBy()
    {
        if ( in_array($this->pagination_order_col, $this->sortby_cols) ) {
            $this->model = $this->model->orderBy($this->pagination_order_col, $this->pagination_order_by);
        }

        return $this;
    }

    /**
     * Extending Query
     *
     * @param Closure|null $closure
     * @return self
     */
    public function extendQuery(?Closure $closure = null)
    {
        if (!$closure) {
            return $this;
        }

        $this->model = $closure($this->model);
        return $this;
    }
}