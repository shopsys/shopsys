### Writing component documentation

https://react-styleguidist.js.org/docs/cookbook

https://react-styleguidist.js.org/docs/documenting/

### How to define propTypes, default props

At the end of component you can define default props, which will be pre-filled only if no props are passed to component. 

```plain
SsfwButton.defaultProps = {
    additionalClassName: 'default',
    type: 'button'
};
 ```
You can even check if passed props are in correct type:

```plain
SsfwButton.propTypes = {
    name: PropTypes.string.isRequired,
    title: PropTypes.string.isRequired,
    type: PropTypes.string,
};
```

For more info and more types see: 

https://reactjs.org/docs/typechecking-with-proptypes.html

This validation is NOT BREAKING - it will generate page - error text will occur in console output. 