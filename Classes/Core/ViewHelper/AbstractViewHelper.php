<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\ViewHelper;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 */

/**
 * The abstract base class for all view helpers.
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
abstract class AbstractViewHelper implements \F3\Fluid\Core\ViewHelper\ViewHelperInterface {

	/**
	 * TRUE if arguments have already been initialized
	 * @var boolean
	 */
	private $argumentsInitialized = FALSE;

	/**
	 * Stores all \F3\Fluid\ArgumentDefinition instances
	 * @var array
	 */
	private $argumentDefinitions = array();

	/**
	 * Current view helper node
	 * @var \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
	 */
	private $viewHelperNode;

	/**
	 * Arguments accessor.
	 * @var \F3\Fluid\Core\ViewHelper\Arguments
	 */
	protected $arguments;

	/**
	 * Current variable container reference.
	 * @var \F3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 */
	protected $variableContainer;

	/**
	 * @var \F3\Fluid\Core\ViewHelper\ViewHelperContext $viewHelperContext
	 */
	protected $viewHelperContext;

	/**
	 * Validator resolver
	 * @var \F3\FLOW3\Validation\ValidatorResolver
	 */
	private $validatorResolver;

	/**
	 * Reflection service
	 * @var \F3\FLOW3\Reflection\Service
	 */
	private $reflectionService;

	/**
	 * @param \F3\Fluid\Core\ViewHelper\Arguments
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @internal
	 */
	public function setArguments(\F3\Fluid\Core\ViewHelper\Arguments $arguments) {
		$this->arguments = $arguments;
	}

	/**
	 * @param \F3\Fluid\Core\ViewHelper\TemplateVariableContainer Variable Container to be used for rendering
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @internal
	 */
	public function setVariableContainer(\F3\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer) {
		$this->variableContainer = $variableContainer;
	}

	/**
	 * @param \F3\Fluid\Core\ViewHelper\ViewHelperContext $viewHelperContext
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @internal
	 */
	public function setViewHelperContext($viewHelperContext) {
		return $this->viewHelperContext = $viewHelperContext;
	}

	/**
	 * Inject a validator resolver
	 * @param \F3\FLOW3\Validation\ValidatorResolver $validatorResolver Validator Resolver
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function injectValidatorResolver(\F3\FLOW3\Validation\ValidatorResolver $validatorResolver) {
		$this->validatorResolver = $validatorResolver;
	}

	/**
	 * Inject a Reflection service
	 * @param \F3\FLOW3\Reflection\Service $reflectionService Reflection service
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function injectReflectionService(\F3\FLOW3\Reflection\Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * Register a new argument. Call this method from your ViewHelper subclass
	 * inside the initializeArguments() method.
	 *
	 * @param string $name Name of the argument
	 * @param string $type Type of the argument
	 * @param string $description Description of the argument
	 * @param boolean $required If TRUE, argument is required. Defaults to FALSE.
	 * @param mixed $defaultValue Default value of argument
	 * @return \F3\Fluid\Core\ViewHelper\AbstractViewHelper $this, to allow chaining.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @todo Object Factory usage!
	 */
	protected function registerArgument($name, $type, $description, $required = FALSE, $defaultValue = NULL) {
		$this->argumentDefinitions[$name] = new \F3\Fluid\Core\ViewHelper\ArgumentDefinition($name, $type, $description, $required, $defaultValue);
		return $this;
	}

	/**
	 * Sets all needed attributes needed for the rendering. Called by the
	 * framework. Populates $this->viewHelperNode
	 *
	 * @param \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode $node View Helper node to be set.
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	final public function setViewHelperNode(\F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode $node) {
		$this->viewHelperNode = $node;
	}

	/**
	 * Initializes the view helper before invoking the render method.
	 *
	 * Override this method to solve tasks before the view helper content is rendered.
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function initialize() {
	}

	/**
	 * Helper method which triggers the rendering of everything between the
	 * opening and the closing tag.
	 *
	 * @return mixed The finally rendered child nodes.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function renderChildren() {
		return $this->viewHelperNode->evaluateChildNodes();
	}

	/**
	 * Initialize all arguments and return them
	 *
	 * @return array Array of F3\Fluid\Core\ViewHelper\ArgumentDefinition instances.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function prepareArguments() {
		if (!$this->argumentsInitialized) {
			$this->registerRenderMethodArguments();
			$this->initializeArguments();
			$this->argumentsInitialized = TRUE;
		}
		return $this->argumentDefinitions;
	}

	/**
	 * Register method arguments for "render" by analysing the doc comment above.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	private function registerRenderMethodArguments() {
		$methodParameters = $this->reflectionService->getMethodParameters(get_class($this), 'render');
		if (count($methodParameters) === 0) {
			return;
		}
		$methodTags = $this->reflectionService->getMethodTagsValues(get_class($this), 'render');

		$paramAnnotations = array();
		if (isset($methodTags['param'])) {
			$paramAnnotations = $methodTags['param'];
		}

		$i = 0;
		foreach ($methodParameters as $parameterName => $parameterInfo) {
			$dataType = NULL;
			if (isset($parameterInfo['type'])) {
				$dataType = $parameterInfo['type'];
			} elseif ($parameterInfo['array']) {
				$dataType = 'array';
			}
			if ($dataType === NULL) {
				throw new \F3\Fluid\Core\Parser\Exception('could not determine type of argument "' . $parameterName .'" of the render-method in ViewHelper "' . get_class($this) . '". Either the methods docComment is invalid or some PHP optimizer strips off comments.', 1242292003);
			}

			$description = '';
			if (isset($paramAnnotations[$i])) {
				$explodedAnnotation = explode(' ', $paramAnnotations[$i]);
				array_shift($explodedAnnotation);
				array_shift($explodedAnnotation);
				$description = implode(' ', $explodedAnnotation);
			}
			$defaultValue = NULL;
			if (isset($parameterInfo['defaultValue'])) {
				$defaultValue = $parameterInfo['defaultValue'];
			}
			$this->argumentDefinitions[$parameterName] = new \F3\Fluid\Core\ViewHelper\ArgumentDefinition($parameterName, $dataType, $description, ($parameterInfo['optional'] === FALSE), $defaultValue, TRUE);
			$i++;
		}
	}

	/**
	 * Validate arguments, and throw exception if arguments do not validate.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @internal
	 */
	public function validateArguments() {
		$argumentDefinitions = $this->prepareArguments();
		if (!count($argumentDefinitions)) return;

		foreach ($argumentDefinitions as $argumentName => $registeredArgument) {
			if ($this->arguments->offsetExists($argumentName)) {
				$type = $registeredArgument->getType();
				if ($this->arguments[$argumentName] === $registeredArgument->getDefaultValue()) continue;

				if ($type === 'array') {
					if (!is_array($this->arguments[$argumentName]) && !$this->arguments[$argumentName] instanceof \ArrayAccess && !$this->arguments[$argumentName] instanceof \Traversable) {
						throw new \F3\Fluid\Core\RuntimeException('The argument "' . $argumentName . '" was registered with type "array", but is of type "' . gettype($this->arguments[$argumentName]) . '" in view helper "' . get_class($this) . '".', 1237900529);
					}
				} elseif ($type === 'boolean') {
					if (!is_bool($this->arguments[$argumentName])) {
						throw new \F3\Fluid\Core\RuntimeException('The argument "' . $argumentName . '" was registered with type "boolean", but is of type "' . gettype($this->arguments[$argumentName]) . '" in view helper "' . get_class($this) . '".', 1240227732);
					}
				} else {
					$validator = $this->validatorResolver->createValidator($type);
					if (is_null($validator)) {
						throw new \F3\Fluid\Core\RuntimeException('No validator found for argument name "' . $argumentName . '" with type "' . $type . '" in view helper "' . get_class($this) . '".', 1237900534);
					}
					if (!$validator->isValid($this->arguments[$argumentName])) {
						throw new \F3\Fluid\Core\RuntimeException('Validation for argument name "' . $argumentName . '" in view helper "' . get_class($this) . '" FAILED. Expected type: "' . $type . '"; Given: "' . gettype($this->arguments[$argumentName]) . '".', 1237900686);
					}
				}
			}
		}
	}

	/**
	 * Initialize all arguments. You need to override this method and call
	 * $this->registerArgument(...) inside this method, to register all your arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
	}

	/**
	 * Render method you need to implement for your custom view helper.
	 * Available objects at this point are $this->arguments, and $this->variableContainer.
	 *
	 * Besides, you often need $this->renderChildren().
	 *
	 * @return string rendered string, view helper specific
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	//abstract public function render();
}

?>