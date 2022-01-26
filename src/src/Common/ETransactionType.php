<?php

namespace App\Common;

enum ETransactionType: string {
  case Debit = 'debit';
  case Credit = 'credit';
}
