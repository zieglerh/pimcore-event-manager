<?php

use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector;

$skipDependencyInjectionPath = __DIR__ . '/*/DependencyInjection/*';

return RectorConfig::configure()
                   ->withPaths([
                                   __DIR__ . '/src',
                               ])
                   ->withSkip([
                                  __DIR__ . '/*/node_modules/*',
                                  RemoveUselessParamTagRector::class,
                                  RemoveUselessReturnTagRector::class,
                                  MixedTypeRector::class,
                                  RenameParamToMatchTypeRector::class,
                                  RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class,
                                  DisallowedEmptyRuleFixerRector::class,
                                  RemoveUselessVarTagRector::class,
                                  NullToStrictStringFuncCallArgRector::class,
                                  ExplicitBoolCompareRector::class,
                                  DisallowedShortTernaryRuleFixerRector::class,
                                  RenamePropertyToMatchTypeRector::class,
                                  RenameVariableToMatchNewTypeRector::class,
                                  RenameVariableToMatchMethodCallReturnTypeRector::class,
                                  RenameForeachValueVariableToMatchExprVariableRector::class,
                                  RemoveUnusedPrivateMethodParameterRector::class,
                                  SimplifyUselessVariableRector::class      => [
                                      $skipDependencyInjectionPath,
                                  ],
                                  RemoveUnusedVariableAssignRector::class   => [
                                      $skipDependencyInjectionPath,
                                  ],
                              ])
                   ->withPhpSets()
                   ->withPHPStanConfigs([
                                            __DIR__ . '/phpstan.neon',
                                        ])
                   ->withPreparedSets(true, true, true, true, true, true, true, true, true);
