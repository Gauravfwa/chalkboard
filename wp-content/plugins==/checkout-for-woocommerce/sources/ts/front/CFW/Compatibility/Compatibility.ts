import Main           from '../Main';
import LoggingService from '../Services/LoggingService';

abstract class Compatibility {
  private _name;

  /**
   * @param {Main} main The Main object
   * @param {any} params Params for the child class to run on load
   * @param {string} name The name of the compatibility module
   */
  protected constructor( main: Main, params, name: string ) {
      this.name = name;

      LoggingService.logCompatibilityClassLoad( this.name );
  }

  /**
   * Literally anything function. Runs user code.
   *
   * @param {Main} main The Main object
   * @param {any} params Params for the child class to run on load
   */
  abstract load( main: Main, params ): void;

  get name() {
      return this._name;
  }

  set name( value ) {
      this._name = value;
  }
}

export default Compatibility;
