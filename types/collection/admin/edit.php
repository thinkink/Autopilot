{%

    $collections = $app->db->find('common/collections')->toArray();
%}

<div class="uk-grid" data-uk-grid-margin>
    <div class="uk-width-2-3">
        <div class="uk-form-row">
            <label><span class="uk-badge app-badge">@lang('Collection')</span></label>
            <div class="uk-margin-small-top">
                <select class="uk-form-large uk-width-1-1" ng-model="data.collectionId">
                    @foreach($collections as $collection)
                    <option value="{{ $collection['_id'] }}">{{ $collection['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="uk-width-1-3">
        <div class="uk-form-row">
            <label><span class="uk-text-small">@lang('Items on list view')</span></label>
            <div class="uk-margin-small-top">
                <input type="text" class="uk-form-large uk-width-1-1" ng-model="data.listItemsLimit" placeholder="10">
            </div>
        </div>
    </div>
</div>