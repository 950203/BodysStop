<?php

abstract class Repository extends Model
{
    abstract public function obtenerTodos(): array;
}
