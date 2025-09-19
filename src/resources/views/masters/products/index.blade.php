@php($title = '商品一覧')
@php($createUrl = route('products.create'))
@include('masters._frame', [
  'title' => $title,
  'createUrl' => $createUrl,
  'slot' => view('masters.products._table', compact('rows')),
])
