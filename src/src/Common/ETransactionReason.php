<?php

namespace App\Common;

enum ETransactionReason: string {
  case Stock = 'stock';
  case Refund = 'refund';
}
