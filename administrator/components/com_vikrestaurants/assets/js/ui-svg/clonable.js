/**
 * UIClonable class.
 * Interface to use every time an object owns nested classes
 * that can be cloned.
 */
class UIClonable {

	/**
	 * Clones this object by returning a new instance
	 * with the same properties.
	 *
	 * @return 	UIClonable
	 */
	clone() {
		// use default method
		return Object.createClone(this);
	}
	
}
