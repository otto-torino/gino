var searchData=
[
  ['datatype',['dataType',['../class_gino_1_1_model.html#a36d27c7d4e163b134cd825743594c040',1,'Gino::Model']]],
  ['date',['date',['../class_gino_1_1_input_form.html#ae704e1de4eec2caf8bbd69f767d54b1d',1,'Gino::InputForm']]],
  ['datediff',['dateDiff',['../namespace_gino.html#ac94f1c8baf3b8d326f312e4349cb2fee',1,'Gino']]],
  ['datetodbdate',['dateToDbDate',['../namespace_gino.html#a21460d461adaa1e5d161b45f4cf9cf28',1,'Gino']]],
  ['dbdatetimetodate',['dbDatetimeToDate',['../namespace_gino.html#a70970d4f84332a34c612dc0a669adfaf',1,'Gino']]],
  ['dbdatetimetotime',['dbDatetimeToTime',['../namespace_gino.html#ab2ae4f19cd577bc8abba3284fe790a95',1,'Gino']]],
  ['dbdatetodate',['dbDateToDate',['../namespace_gino.html#a9d61427f840ca49620490e32f4f6bdfa',1,'Gino']]],
  ['dbnumbertonumber',['dbNumberToNumber',['../namespace_gino.html#ae78d5023830a5e8a7de06d6245cd0576',1,'Gino']]],
  ['dbtimetotime',['dbTimeToTime',['../namespace_gino.html#a2f2363a283070342935ee1485d7c796d',1,'Gino']]],
  ['decode_5fparams',['decode_params',['../namespace_gino.html#a503d0121a7e96123495d9a1df614bdff',1,'Gino']]],
  ['defaultcaptcha',['defaultCaptcha',['../class_gino_1_1_form.html#a67e085f6635fd069e5bbbd257f170863',1,'Gino::Form']]],
  ['defaultname',['defaultName',['../class_gino_1_1_directory_field.html#abb106f3918d722d068f3a785a27508c0',1,'Gino::DirectoryField']]],
  ['define',['define',['../mootools-1_84_80-yc_8js.html#aea76d8890d5ba27827c27ae90c45a370',1,'mootools-1.4.0-yc.js']]],
  ['defineextension',['defineExtension',['../matchbrackets_8js.html#aaf0ce8f9e66a226dc07ccb4f9a5a5678',1,'matchbrackets.js']]],
  ['definekeys',['defineKeys',['../mootools-1_84_80-yc_8js.html#a39e192abc06d37458deb579d279468d2',1,'mootools-1.4.0-yc.js']]],
  ['definemime',['defineMIME',['../clike_8js.html#a2518683bb7af6c6e9db357e02e8b54e7',1,'defineMIME(&quot;text/x-java&quot;,{name:&quot;clike&quot;, keywords:words(&quot;abstract assert boolean break byte case catch char class const continue default &quot;+&quot;do double else enum extends final finally float for goto if implements import &quot;+&quot;instanceof int interface long native new package private protected public &quot;+&quot;return short static strictfp super switch synchronized this throw throws transient &quot;+&quot;try void volatile while&quot;), blockKeywords:words(&quot;catch class do else finally for if switch try while&quot;), atoms:words(&quot;true false null&quot;), hooks:{&quot;@&quot;:function(stream){stream.eatWhile(/[\w\$_]/);return&quot;meta&quot;;}}}):&#160;clike.js'],['../htmlembedded_8js.html#aca61e7be460749574d56b187ef6c6f3a',1,'defineMIME(&quot;application/x-ejs&quot;,{name:&quot;htmlembedded&quot;, scriptingModeSpec:&quot;javascript&quot;}):&#160;htmlembedded.js'],['../javascript_8js.html#a9c0cae9bbb19e947588404720f4c09e4',1,'defineMIME(&quot;text/javascript&quot;,&quot;javascript&quot;):&#160;javascript.js']]],
  ['definemode',['defineMode',['../clike_8js.html#a8ce1c2a0c8b8f03b862d213b099a503e',1,'defineMode(&quot;clike&quot;, function(config, parserConfig){var indentUnit=config.indentUnit, statementIndentUnit=parserConfig.statementIndentUnit||indentUnit, dontAlignCalls=parserConfig.dontAlignCalls, keywords=parserConfig.keywords||{}, builtin=parserConfig.builtin||{}, blockKeywords=parserConfig.blockKeywords||{}, atoms=parserConfig.atoms||{}, hooks=parserConfig.hooks||{}, multiLineStrings=parserConfig.multiLineStrings;var isOperatorChar=/[+\-*&amp;%=&lt;&gt;!?|\/]/;var curPunc;function tokenBase(stream, state){var ch=stream.next();if(hooks[ch]){var result=hooks[ch](stream, state);if(result!==false) return result;}if(ch== &apos;&quot;&apos; || ch == &quot;&apos;&quot;) { state.tokenize = tokenString(ch); return state.tokenize(stream, state); } if (/[\[\]{}\(\),;\:\.]/.test(ch)) { curPunc = ch; return null; } if (/\d/.test(ch)) { stream.eatWhile(/[\w\.]/); return &quot;number&quot;; } if (ch == &quot;/&quot;) { if (stream.eat(&quot;*&quot;)) { state.tokenize = tokenComment; return tokenComment(stream, state); } if (stream.eat(&quot;/&quot;)) { stream.skipToEnd(); return &quot;comment&quot;; } } if (isOperatorChar.test(ch)) { stream.eatWhile(isOperatorChar); return &quot;operator&quot;; } stream.eatWhile(/[\w\$_]/); var cur = stream.current(); if (keywords.propertyIsEnumerable(cur)) { if (blockKeywords.propertyIsEnumerable(cur)) curPunc = &quot;newstatement&quot;; return &quot;keyword&quot;; } if (builtin.propertyIsEnumerable(cur)) { if (blockKeywords.propertyIsEnumerable(cur)) curPunc = &quot;newstatement&quot;; return &quot;builtin&quot;; } if (atoms.propertyIsEnumerable(cur)) return &quot;atom&quot;; return &quot;variable&quot;; } function tokenString(quote) { return function(stream, state) { var escaped = false, next, end = false; while ((next = stream.next()) != null) { if (next == quote &amp;&amp; !escaped) {end = true; break;} escaped = !escaped &amp;&amp; next == &quot;\\&quot;; } if (end || !(escaped || multiLineStrings)) state.tokenize = null; return &quot;string&quot;; }; } function tokenComment(stream, state) { var maybeEnd = false, ch; while (ch = stream.next()) { if (ch == &quot;/&quot; &amp;&amp; maybeEnd) { state.tokenize = null; break; } maybeEnd = (ch == &quot;*&quot;); } return &quot;comment&quot;; } function Context(indented, column, type, align, prev) { this.indented = indented; this.column = column; this.type = type; this.align = align; this.prev = prev; } function pushContext(state, col, type) { var indent = state.indented; if (state.context &amp;&amp; state.context.type == &quot;statement&quot;) indent = state.context.indented; return state.context = new Context(indent, col, type, null, state.context); } function popContext(state) { var t = state.context.type; if (t == &quot;)&quot; || t == &quot;]&quot; || t == &quot;}&quot;) state.indented = state.context.indented; return state.context = state.context.prev; }  return { startState: function(basecolumn) { return { tokenize: null, context: new Context((basecolumn || 0) - indentUnit, 0, &quot;top&quot;, false), indented: 0, startOfLine: true }; }, token: function(stream, state) { var ctx = state.context; if (stream.sol()) { if (ctx.align == null) ctx.align = false; state.indented = stream.indentation(); state.startOfLine = true; } if (stream.eatSpace()) return null; curPunc = null; var style = (state.tokenize || tokenBase)(stream, state); if (style == &quot;comment&quot; || style == &quot;meta&quot;) return style; if (ctx.align == null) ctx.align = true; if ((curPunc == &quot;;&quot; || curPunc == &quot;:&quot; || curPunc == &quot;,&quot;) &amp;&amp; ctx.type == &quot;statement&quot;) popContext(state); else if (curPunc == &quot;{&quot;) pushContext(state, stream.column(), &quot;}&quot;); else if (curPunc == &quot;[&quot;) pushContext(state, stream.column(), &quot;]&quot;); else if (curPunc == &quot;(&quot;) pushContext(state, stream.column(), &quot;)&quot;); else if (curPunc == &quot;}&quot;) { while (ctx.type == &quot;statement&quot;) ctx = popContext(state); if (ctx.type == &quot;}&quot;) ctx = popContext(state); while (ctx.type == &quot;statement&quot;) ctx = popContext(state); } else if (curPunc == ctx.type) popContext(state); else if (((ctx.type == &quot;}&quot; || ctx.type == &quot;top&quot;) &amp;&amp; curPunc != &apos;;&apos;) || (ctx.type == &quot;statement&quot; &amp;&amp; curPunc == &quot;newstatement&quot;)) pushContext(state, stream.column(), &quot;statement&quot;); state.startOfLine = false; return style; }, indent: function(state, textAfter) { if (state.tokenize != tokenBase &amp;&amp; state.tokenize != null) return CodeMirror.Pass; var ctx = state.context, firstChar = textAfter &amp;&amp; textAfter.charAt(0); if (ctx.type == &quot;statement&quot; &amp;&amp; firstChar == &quot;}&quot;) ctx = ctx.prev; var closing = firstChar == ctx.type; if (ctx.type == &quot;statement&quot;) return ctx.indented + (firstChar == &quot;{&quot; ? 0 : statementIndentUnit); else if (ctx.align &amp;&amp; (!dontAlignCalls || ctx.type != &quot;)&quot;)) return ctx.column + (closing ? 0 : 1); else if (ctx.type == &quot;)&quot; &amp;&amp; !closing) return ctx.indented + statementIndentUnit; else return ctx.indented + (closing ? 0 : indentUnit); }, electricChars: &quot;{}&quot;, blockCommentStart: &quot;/*&quot;, blockCommentEnd: &quot;*/&quot;, lineComment: &quot;//&quot;, fold: &quot;brace&quot; }; }):&#160;clike.js'],['../htmlembedded_8js.html#a042ace5df5d58e489afe891782bece3d',1,'defineMode(&quot;htmlembedded&quot;, function(config, parserConfig){var scriptStartRegex=parserConfig.scriptStartRegex||/^&lt;%/i, scriptEndRegex=parserConfig.scriptEndRegex||/^%&gt;/i;var scriptingMode, htmlMixedMode;function htmlDispatch(stream, state){if(stream.match(scriptStartRegex, false)){state.token=scriptingDispatch;return scriptingMode.token(stream, state.scriptState);}else return htmlMixedMode.token(stream, state.htmlState);}function scriptingDispatch(stream, state){if(stream.match(scriptEndRegex, false)){state.token=htmlDispatch;return htmlMixedMode.token(stream, state.htmlState);}else return scriptingMode.token(stream, state.scriptState);}return{startState:function(){scriptingMode=scriptingMode||CodeMirror.getMode(config, parserConfig.scriptingModeSpec);htmlMixedMode=htmlMixedMode||CodeMirror.getMode(config,&quot;htmlmixed&quot;);return{token:parserConfig.startOpen?scriptingDispatch:htmlDispatch, htmlState:CodeMirror.startState(htmlMixedMode), scriptState:CodeMirror.startState(scriptingMode)};}, token:function(stream, state){return state.token(stream, state);}, indent:function(state, textAfter){if(state.token==htmlDispatch) return htmlMixedMode.indent(state.htmlState, textAfter);else if(scriptingMode.indent) return scriptingMode.indent(state.scriptState, textAfter);}, copyState:function(state){return{token:state.token, htmlState:CodeMirror.copyState(htmlMixedMode, state.htmlState), scriptState:CodeMirror.copyState(scriptingMode, state.scriptState)};}, electricChars:&quot;/{}:&quot;, innerMode:function(state){if(state.token==scriptingDispatch) return{state:state.scriptState, mode:scriptingMode};else return{state:state.htmlState, mode:htmlMixedMode};}};},&quot;htmlmixed&quot;):&#160;htmlembedded.js'],['../javascript_8js.html#abdfc185e31bc9636dee98568e91cae67',1,'defineMode(&quot;javascript&quot;, function(config, parserConfig){var indentUnit=config.indentUnit;var statementIndent=parserConfig.statementIndent;var jsonMode=parserConfig.json;var isTS=parserConfig.typescript;var keywords=function(){function kw(type){return{type:type, style:&quot;keyword&quot;};}var A=kw(&quot;keyword a&quot;), B=kw(&quot;keyword b&quot;), C=kw(&quot;keyword c&quot;);var operator=kw(&quot;operator&quot;), atom={type:&quot;atom&quot;, style:&quot;atom&quot;};var jsKeywords={&quot;if&quot;:kw(&quot;if&quot;),&quot;while&quot;:A,&quot;with&quot;:A,&quot;else&quot;:B,&quot;do&quot;:B,&quot;try&quot;:B,&quot;finally&quot;:B,&quot;return&quot;:C,&quot;break&quot;:C,&quot;continue&quot;:C,&quot;new&quot;:C,&quot;delete&quot;:C,&quot;throw&quot;:C,&quot;var&quot;:kw(&quot;var&quot;),&quot;const&quot;:kw(&quot;var&quot;),&quot;let&quot;:kw(&quot;var&quot;),&quot;function&quot;:kw(&quot;function&quot;),&quot;catch&quot;:kw(&quot;catch&quot;),&quot;for&quot;:kw(&quot;for&quot;),&quot;switch&quot;:kw(&quot;switch&quot;),&quot;case&quot;:kw(&quot;case&quot;),&quot;default&quot;:kw(&quot;default&quot;),&quot;in&quot;:operator,&quot;typeof&quot;:operator,&quot;instanceof&quot;:operator,&quot;true&quot;:atom,&quot;false&quot;:atom,&quot;null&quot;:atom,&quot;undefined&quot;:atom,&quot;NaN&quot;:atom,&quot;Infinity&quot;:atom,&quot;this&quot;:kw(&quot;this&quot;)};if(isTS){var type={type:&quot;variable&quot;, style:&quot;variable-3&quot;};var tsKeywords={&quot;interface&quot;:kw(&quot;interface&quot;),&quot;class&quot;:kw(&quot;class&quot;),&quot;extends&quot;:kw(&quot;extends&quot;),&quot;constructor&quot;:kw(&quot;constructor&quot;),&quot;public&quot;:kw(&quot;public&quot;),&quot;private&quot;:kw(&quot;private&quot;),&quot;protected&quot;:kw(&quot;protected&quot;),&quot;static&quot;:kw(&quot;static&quot;),&quot;super&quot;:kw(&quot;super&quot;),&quot;string&quot;:type,&quot;number&quot;:type,&quot;bool&quot;:type,&quot;any&quot;:type};for(var attr in tsKeywords){jsKeywords[attr]=tsKeywords[attr];}}return jsKeywords;}();var isOperatorChar=/[+\-*&amp;%=&lt;&gt;!?|~^]/;function chain(stream, state, f){state.tokenize=f;return f(stream, state);}function nextUntilUnescaped(stream, end){var escaped=false, next;while((next=stream.next())!=null){if(next==end &amp;&amp;!escaped) return false;escaped=!escaped &amp;&amp;next==&quot;\\&quot;;}return escaped;}var type, content;function ret(tp, style, cont){type=tp;content=cont;return style;}function jsTokenBase(stream, state){var ch=stream.next();if(ch== &apos;&quot;&apos; || ch == &quot;&apos;&quot;) return chain(stream, state, jsTokenString(ch)); else if (ch == &quot;.&quot; &amp;&amp; stream.match(/^\d+(?:[eE][+\-]?\d+)?/)) return ret(&quot;number&quot;, &quot;number&quot;); else if (/[\[\]{}\(\),;\:\.]/.test(ch)) return ret(ch); else if (ch == &quot;0&quot; &amp;&amp; stream.eat(/x/i)) { stream.eatWhile(/[\da-f]/i); return ret(&quot;number&quot;, &quot;number&quot;); } else if (/\d/.test(ch)) { stream.match(/^\d*(?:\.\d*)?(?:[eE][+\-]?\d+)?/); return ret(&quot;number&quot;, &quot;number&quot;); } else if (ch == &quot;/&quot;) { if (stream.eat(&quot;*&quot;)) { return chain(stream, state, jsTokenComment); } else if (stream.eat(&quot;/&quot;)) { stream.skipToEnd(); return ret(&quot;comment&quot;, &quot;comment&quot;); } else if (state.lastType == &quot;operator&quot; || state.lastType == &quot;keyword c&quot; ||
               /^[\[{}\(,;:]$/.test(state.lastType)) {
        nextUntilUnescaped(stream, &quot;/&quot;);
        stream.eatWhile(/[gimy]/); 
        return ret(&quot;regexp&quot;, &quot;string-2&quot;);
      }
      else {
        stream.eatWhile(isOperatorChar);
        return ret(&quot;operator&quot;, null, stream.current());
      }
    }
    else if (ch == &quot;#&quot;) {
      stream.skipToEnd();
      return ret(&quot;error&quot;, &quot;error&quot;);
    }
    else if (isOperatorChar.test(ch)) {
      stream.eatWhile(isOperatorChar);
      return ret(&quot;operator&quot;, null, stream.current());
    }
    else {
      stream.eatWhile(/[\w\$_]/);
      var word = stream.current(), known = keywords.propertyIsEnumerable(word) &amp;&amp; keywords[word];
      return (known &amp;&amp; state.lastType != &quot;.&quot;) ? ret(known.type, known.style, word) :
                     ret(&quot;variable&quot;, &quot;variable&quot;, word);
    }
  }

  function jsTokenString(quote) {
    return function(stream, state) {
      if (!nextUntilUnescaped(stream, quote))
        state.tokenize = jsTokenBase;
      return ret(&quot;string&quot;, &quot;string&quot;);
    };
  }

  function jsTokenComment(stream, state) {
    var maybeEnd = false, ch;
    while (ch = stream.next()) {
      if (ch == &quot;/&quot; &amp;&amp; maybeEnd) {
        state.tokenize = jsTokenBase;
        break;
      }
      maybeEnd = (ch == &quot;*&quot;);
    }
    return ret(&quot;comment&quot;, &quot;comment&quot;);
  }

  

  var atomicTypes = {&quot;atom&quot;: true, &quot;number&quot;: true, &quot;variable&quot;: true, &quot;string&quot;: true, &quot;regexp&quot;: true, &quot;this&quot;: true};

  function JSLexical(indented, column, type, align, prev, info) {
    this.indented = indented;
    this.column = column;
    this.type = type;
    this.prev = prev;
    this.info = info;
    if (align != null) this.align = align;
  }

  function inScope(state, varname) {
    for (var v = state.localVars; v; v = v.next)
      if (v.name == varname) return true;
  }

  function parseJS(state, style, type, content, stream) {
    var cc = state.cc;
    
    
    cx.state = state; cx.stream = stream; cx.marked = null, cx.cc = cc;

    if (!state.lexical.hasOwnProperty(&quot;align&quot;))
      state.lexical.align = true;

    while(true) {
      var combinator = cc.length ? cc.pop() : jsonMode ? expression : statement;
      if (combinator(type, content)) {
        while(cc.length &amp;&amp; cc[cc.length - 1].lex)
          cc.pop()();
        if (cx.marked) return cx.marked;
        if (type == &quot;variable&quot; &amp;&amp; inScope(state, content)) return &quot;variable-2&quot;;
        return style;
      }
    }
  }

  

  var cx = {state: null, column: null, marked: null, cc: null};
  function pass() {
    for (var i = arguments.length - 1; i &gt;= 0; i--) cx.cc.push(arguments[i]);
  }
  function cont() {
    pass.apply(null, arguments);
    return true;
  }
  function register(varname) {
    function inList(list) {
      for (var v = list; v; v = v.next)
        if (v.name == varname) return true;
      return false;
    }
    var state = cx.state;
    if (state.context) {
      cx.marked = &quot;def&quot;;
      if (inList(state.localVars)) return;
      state.localVars = {name: varname, next: state.localVars};
    } else {
      if (inList(state.globalVars)) return;
      state.globalVars = {name: varname, next: state.globalVars};
    }
  }

  

  var defaultVars = {name: &quot;this&quot;, next: {name: &quot;arguments&quot;}};
  function pushcontext() {
    cx.state.context = {prev: cx.state.context, vars: cx.state.localVars};
    cx.state.localVars = defaultVars;
  }
  function popcontext() {
    cx.state.localVars = cx.state.context.vars;
    cx.state.context = cx.state.context.prev;
  }
  function pushlex(type, info) {
    var result = function() {
      var state = cx.state, indent = state.indented;
      if (state.lexical.type == &quot;stat&quot;) indent = state.lexical.indented;
      state.lexical = new JSLexical(indent, cx.stream.column(), type, null, state.lexical, info);
    };
    result.lex = true;
    return result;
  }
  function poplex() {
    var state = cx.state;
    if (state.lexical.prev) {
      if (state.lexical.type == &quot;)&quot;)
        state.indented = state.lexical.indented;
      state.lexical = state.lexical.prev;
    }
  }
  poplex.lex = true;

  function expect(wanted) {
    return function(type) {
      if (type == wanted) return cont();
      else if (wanted == &quot;;&quot;) return pass();
      else return cont(arguments.callee);
    };
  }

  function statement(type) {
    if (type == &quot;var&quot;) return cont(pushlex(&quot;vardef&quot;), vardef1, expect(&quot;;&quot;), poplex);
    if (type == &quot;keyword a&quot;) return cont(pushlex(&quot;form&quot;), expression, statement, poplex);
    if (type == &quot;keyword b&quot;) return cont(pushlex(&quot;form&quot;), statement, poplex);
    if (type == &quot;{&quot;) return cont(pushlex(&quot;}&quot;), block, poplex);
    if (type == &quot;;&quot;) return cont();
    if (type == &quot;if&quot;) return cont(pushlex(&quot;form&quot;), expression, statement, poplex, maybeelse);
    if (type == &quot;function&quot;) return cont(functiondef);
    if (type == &quot;for&quot;) return cont(pushlex(&quot;form&quot;), expect(&quot;(&quot;), pushlex(&quot;)&quot;), forspec1, expect(&quot;)&quot;),
                                   poplex, statement, poplex);
    if (type == &quot;variable&quot;) return cont(pushlex(&quot;stat&quot;), maybelabel);
    if (type == &quot;switch&quot;) return cont(pushlex(&quot;form&quot;), expression, pushlex(&quot;}&quot;, &quot;switch&quot;), expect(&quot;{&quot;),
                                      block, poplex, poplex);
    if (type == &quot;case&quot;) return cont(expression, expect(&quot;:&quot;));
    if (type == &quot;default&quot;) return cont(expect(&quot;:&quot;));
    if (type == &quot;catch&quot;) return cont(pushlex(&quot;form&quot;), pushcontext, expect(&quot;(&quot;), funarg, expect(&quot;)&quot;),
                                     statement, poplex, popcontext);
    return pass(pushlex(&quot;stat&quot;), expression, expect(&quot;;&quot;), poplex);
  }
  function expression(type) {
    return expressionInner(type, false);
  }
  function expressionNoComma(type) {
    return expressionInner(type, true);
  }
  function expressionInner(type, noComma) {
    var maybeop = noComma ? maybeoperatorNoComma : maybeoperatorComma;
    if (atomicTypes.hasOwnProperty(type)) return cont(maybeop);
    if (type == &quot;function&quot;) return cont(functiondef);
    if (type == &quot;keyword c&quot;) return cont(noComma ? maybeexpressionNoComma : maybeexpression);
    if (type == &quot;(&quot;) return cont(pushlex(&quot;)&quot;), maybeexpression, expect(&quot;)&quot;), poplex, maybeop);
    if (type == &quot;operator&quot;) return cont(noComma ? expressionNoComma : expression);
    if (type == &quot;[&quot;) return cont(pushlex(&quot;]&quot;), commasep(expressionNoComma, &quot;]&quot;), poplex, maybeop);
    if (type == &quot;{&quot;) return cont(pushlex(&quot;}&quot;), commasep(objprop, &quot;}&quot;), poplex, maybeop);
    return cont();
  }
  function maybeexpression(type) {
    if (type.match(/[;\}\)\],]/)) return pass(); return pass(expression); } function maybeexpressionNoComma(type) { if (type.match(/[;\}\)\],]/)) return pass():&#160;javascript.js']]],
  ['defineoption',['defineOption',['../matchbrackets_8js.html#a9527cc37b2b6ea3615a86a491187c3bc',1,'matchbrackets.js']]],
  ['defineparsers',['defineParsers',['../mootools-1_84_80-yc_8js.html#a7f98816ed78d1e4592257e52ce18b1e7',1,'defineParsers(&quot;%Y([-./]%m([-./]%d((T| )%X)?)?)?&quot;,&quot;%Y%m%d(T%H(%M%S?)?)?&quot;,&quot;%x( %X)?&quot;,&quot;%d%o( %b( %Y)?)?( %X)?&quot;,&quot;%b( %d%o)?( %Y)?( %X)?&quot;,&quot;%Y %b( %d%o( %X)?)?&quot;,&quot;%o %b %d %X %z %Y&quot;,&quot;%T&quot;,&quot;%H:%M( ?%p)?&quot;):&#160;mootools-1.4.0-yc.js'],['../mootools-1_84_80-yc_8js.html#a6628cbb1f20914860cd6e9c818c7fd68',1,'defineParsers({re:/^(?:tod|tom|yes)/i, handler:function(a){var b=new Date().clearTime();switch(a[0]){case&quot;tom&quot;:return b.increment();case&quot;yes&quot;:return b.decrement();default:return b;}}},{re:/^(next|last)([a-z]+)$/i, handler:function(e){var f=new Date().clearTime();var b=f.getDay();var c=Date.parseDay(e[2], true);var a=c-b;if(c&lt;=b){a+=7;}if(e[1]==&quot;last&quot;){a-=7;}return f.set(&quot;date&quot;, f.getDate()+a);}}).alias(&quot;timeAgoInWords&quot;:&#160;mootools-1.4.0-yc.js']]],
  ['definepseudo',['definePseudo',['../mootools-1_84_80-yc_8js.html#a20631b0cdb36bb98278deabe61cad309',1,'mootools-1.4.0-yc.js']]],
  ['delete',['delete',['../class_gino_1_1_app_1_1_attachment_1_1_attachment_ctg.html#a13bdffdd926f26b825ea57066334ff01',1,'Gino\App\Attachment\AttachmentCtg\delete()'],['../class_gino_1_1_app_1_1_auth_1_1_user.html#a13bdffdd926f26b825ea57066334ff01',1,'Gino\App\Auth\User\delete()'],['../class_gino_1_1_app_1_1_page_1_1_page_entry.html#a13bdffdd926f26b825ea57066334ff01',1,'Gino\App\Page\PageEntry\delete()'],['../class_gino_1_1_cache.html#afae203993e3c8db00e9a53a07048e260',1,'Gino\Cache\delete()'],['../interface_gino_1_1_db_manager.html#a6a4beed12bcd9c94b2ce03f46045a061',1,'Gino\DbManager\delete()'],['../class_gino_1_1_directory_field.html#a13bdffdd926f26b825ea57066334ff01',1,'Gino\DirectoryField\delete()'],['../class_gino_1_1_file_field.html#a13bdffdd926f26b825ea57066334ff01',1,'Gino\FileField\delete()'],['../class_gino_1_1_image_field.html#a13bdffdd926f26b825ea57066334ff01',1,'Gino\ImageField\delete()'],['../class_gino_1_1_model.html#a13bdffdd926f26b825ea57066334ff01',1,'Gino\Model\delete()'],['../class_gino_1_1_plugin_1_1mysql.html#a4b34e33eec858b9cd3afdf7ea0d0b67f',1,'Gino\Plugin\mysql\delete()'],['../class_gino_1_1_plugin_1_1sqlsrv.html#a4b34e33eec858b9cd3afdf7ea0d0b67f',1,'Gino\Plugin\sqlsrv\delete()']]],
  ['deleteblocks',['deleteBlocks',['../class_gino_1_1_template.html#a35cc933f68bf1b3a7f7b281af067a960',1,'Gino::Template']]],
  ['deletedbdata',['deleteDbData',['../class_gino_1_1_model.html#ae5ed797010249abdf294d4b6d3a2a1ea',1,'Gino::Model']]],
  ['deletefiledir',['deleteFileDir',['../namespace_gino.html#a194b100b8a00d41e27ac693b7a8a1901',1,'Gino']]],
  ['deletefromctg',['deleteFromCtg',['../class_gino_1_1_app_1_1_attachment_1_1_attachment_item.html#a1832f8e84194bc614dfd51b683f58153',1,'Gino::App::Attachment::AttachmentItem']]],
  ['deletefromentry',['deleteFromEntry',['../class_gino_1_1_app_1_1_page_1_1_page_comment.html#a8faf8a1f0ac5b65902f49eef02c7f2bf',1,'Gino::App::Page::PageComment']]],
  ['deleteinstance',['deleteInstance',['../class_gino_1_1_app_1_1_menu_1_1menu.html#a6322cbb1daf9fef84b08b6fa3344b12d',1,'Gino\App\Menu\menu\deleteInstance()'],['../class_gino_1_1_app_1_1_php_module_view_1_1php_module_view.html#a6322cbb1daf9fef84b08b6fa3344b12d',1,'Gino\App\PhpModuleView\phpModuleView\deleteInstance()'],['../class_gino_1_1_controller.html#a6322cbb1daf9fef84b08b6fa3344b12d',1,'Gino\Controller\deleteInstance()'],['../class_gino_1_1_model.html#af0bb6b35f6fca330595b4e7810ff130c',1,'Gino\Model\deleteInstance()']]],
  ['deleteinstancevoices',['deleteInstanceVoices',['../class_gino_1_1_app_1_1_menu_1_1_menu_voice.html#a892f330fb0e79d6c96271d851a46b1a6',1,'Gino::App::Menu::MenuVoice']]],
  ['deletem2m',['deletem2m',['../class_gino_1_1_model.html#abb4a96a7534e216018facdfae99808da',1,'Gino::Model']]],
  ['deletem2mthrough',['deletem2mthrough',['../class_gino_1_1_model.html#a46df9b281197d4029c6758c8ba1ab84f',1,'Gino::Model']]],
  ['deletem2mthroughfield',['deletem2mthroughField',['../class_gino_1_1_model.html#ae9aa25368ecbc85d2e6720f2f4446fad',1,'Gino::Model']]],
  ['deletemoduleinstance',['deleteModuleInstance',['../class_gino_1_1_app_1_1_module_1_1module.html#a6c3ddf0da8b90ea8c66755666f91599f',1,'Gino::App::Module::module']]],
  ['deletemoreinfo',['deleteMoreInfo',['../class_gino_1_1_app_1_1_auth_1_1_user.html#acfba230a02ef4880effde5c020db9055',1,'Gino::App::Auth::User']]],
  ['deletetranslations',['deleteTranslations',['../class_gino_1_1_translation.html#a31817f81482aa3f4a9c08e43316c1cbf',1,'Gino::Translation']]],
  ['deletevoice',['deleteVoice',['../class_gino_1_1_app_1_1_menu_1_1_menu_voice.html#a53f66ca50f0565bb9429493146ae7195',1,'Gino::App::Menu::MenuVoice']]],
  ['destroy',['destroy',['../class_gino_1_1_session.html#aa118461de946085fe42989193337044a',1,'Gino::Session']]],
  ['detectcodes',['detectCodes',['../class_gino_1_1_locale.html#a604fc5bea08c41f4e3df83a0dc4eb689',1,'Gino::Locale']]],
  ['detectmobile',['detectMobile',['../class_gino_1_1_core.html#ac2d45cea6d2bec605a4bd86192abddf2',1,'Gino::Core']]],
  ['dimensionfile',['dimensionFile',['../class_gino_1_1_form.html#a482322dc203535b7b51cc7f09b1e1585',1,'Gino::Form']]],
  ['dirupload',['dirUpload',['../class_gino_1_1_form.html#a222402beceb294832fced45bc3137d06',1,'Gino::Form']]],
  ['distinct',['distinct',['../interface_gino_1_1_db_manager.html#aaba187d7afd68db7d334ed40acd224ca',1,'Gino\DbManager\distinct()'],['../class_gino_1_1_plugin_1_1mysql.html#a5a7a1e24ebf59c71664b0ceb8633e472',1,'Gino\Plugin\mysql\distinct()'],['../class_gino_1_1_plugin_1_1sqlsrv.html#a5a7a1e24ebf59c71664b0ceb8633e472',1,'Gino\Plugin\sqlsrv\distinct()']]],
  ['domatchbrackets',['doMatchBrackets',['../matchbrackets_8js.html#a09b07060ad74db66071f68eb56459ffb',1,'matchbrackets.js']]],
  ['download',['download',['../namespace_gino.html#a2373a3e521bfb8929ea3aa67a87e43cf',1,'Gino']]],
  ['downloader',['downloader',['../class_gino_1_1_app_1_1_attachment_1_1attachment.html#a7c2181571bc607404c6067d5cd906369',1,'Gino::App::Attachment::attachment']]],
  ['drop',['drop',['../interface_gino_1_1_db_manager.html#aa6f98a0526ecf976d782f1db8eea7296',1,'Gino\DbManager\drop()'],['../class_gino_1_1_plugin_1_1mysql.html#aa6f98a0526ecf976d782f1db8eea7296',1,'Gino\Plugin\mysql\drop()'],['../class_gino_1_1_plugin_1_1sqlsrv.html#aa6f98a0526ecf976d782f1db8eea7296',1,'Gino\Plugin\sqlsrv\drop()']]],
  ['dump',['dump',['../class_gino_1_1_admin_table.html#abe9df294ff3f948d50a96ceb92a5ec5f',1,'Gino\AdminTable\dump()'],['../interface_gino_1_1_db_manager.html#abe9df294ff3f948d50a96ceb92a5ec5f',1,'Gino\DbManager\dump()'],['../class_gino_1_1_plugin_1_1mysql.html#abe9df294ff3f948d50a96ceb92a5ec5f',1,'Gino\Plugin\mysql\dump()'],['../class_gino_1_1_plugin_1_1sqlsrv.html#abe9df294ff3f948d50a96ceb92a5ec5f',1,'Gino\Plugin\sqlsrv\dump()']]],
  ['dumpdatabase',['dumpDatabase',['../interface_gino_1_1_db_manager.html#a5d212c1f99e7c7eba4cc17beb7b2b55f',1,'Gino\DbManager\dumpDatabase()'],['../class_gino_1_1_plugin_1_1mysql.html#a5d212c1f99e7c7eba4cc17beb7b2b55f',1,'Gino\Plugin\mysql\dumpDatabase()'],['../class_gino_1_1_plugin_1_1sqlsrv.html#a5d212c1f99e7c7eba4cc17beb7b2b55f',1,'Gino\Plugin\sqlsrv\dumpDatabase()']]]
];
