# Coding Standards

## File Naming
- Use snake_case for all files

## Database Conventions

## Variable Naming
- **Local variables/methods**: camelCase (first word lowercase)
- **Global/static variables**: PascalCase (first word uppercase)
- **Constants/macros**: SNAKE_CASE (all uppercase)
- **Type definitions**: snake_case ending with `_t`

## Indentation & Spacing
- Use **4-space tabs**
- No indentation for braces
- Indent preprocessor statements to current level
- **CSS**: One style per line, space after colons
- **SQL**: Space after commas in parameter lists
- **Arithmetic**: Spaces around operators (except `++`/`--`)
- **Functions**: No spaces around parentheses, space after commas

## Code Structure
- **Opening braces**: Same line as statement
- **Else statements**: Same line as closing brace
- **Single statements**: No braces required for single line conditionals
- **Switch cases**: Indent cases and content, explicit breaks
- **Comments**: Use `//` for single-line, detailed PHPDoc or JSDoc for functions/classes where applicable

## SQL Standards
- **Keywords**: UPPERCASE (SELECT, FROM, WHERE)
- **Identifiers**: Always quoted with backticks
- **Joins**: Use explicit JOIN syntax, avoid implicit joins
- **Aliases**: Required with AS keyword

## PHP Specific
- Use `[]` instead of `array()`
- Use `<?=$var?>` instead of `<?php echo $var; ?>`
- Avoid `$_REQUEST`, use `$_GET`/`$_POST`
- Use relative operators when possible (`+=`, `*=`)
- Logical operators: `||` and `&&` (not `or`/`and`)
- Conditions: `else if` (not `elseif`)
- Always use `isset()`

## Best Practices
- Verbose naming over abbreviated
- Group arithmetic operations in parentheses
- Explicit switch breaks and fallthrough comments
- Always include return statements
- No gotos or labels
- Trailing commas in arrays/lists
- One CSS declaration per line