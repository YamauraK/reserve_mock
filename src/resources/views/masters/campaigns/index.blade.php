@php($title = '企画一覧')
@php($createUrl = route('campaigns.create'))
@include('masters._frame', [
  'title' => $title,
  'createUrl' => $createUrl,
  'slot' => view('masters.campaigns._table', compact('rows')),
])
