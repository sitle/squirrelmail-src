all: txt html pdf

txt:
	echo "Converting sgml to txt"
	linuxdoc -B txt trans-ref.sgml

html:
	echo "Converting sgml to multipaged html"
	linuxdoc -B html trans-ref.sgml

pdf:
	echo "Converting sgml to pdf"
	linuxdoc -B latex --output=pdf trans-ref.sgml
