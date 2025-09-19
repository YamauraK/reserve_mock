@php($title = '店舗一覧')
@php($createUrl = route('stores.create'))
@include('masters._frame', [
  'title' => $title,
  'createUrl' => $createUrl,
  'slot' => view('masters.stores._table', compact('rows')),
])
