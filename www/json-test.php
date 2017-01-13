<?
$in = '[{
      "profile": "aaa",
      "field": "Notes",
      "value": "val1"
    },
    {
      "profile": "abc",
      "field": "Notes",
      "value": "These are my notes"
    },
    {
      "profile": "rrr",
      "field": "Notes",
      "value": "more notes"
    },
    {
      "profile": "ss77dickslap",
      "field": "Notes",
      "value": "Updated notes"
    }
]';

$out = new stdClass();

if ($in) $out->fields=(json_decode($in));

echo json_encode($out);