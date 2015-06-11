# Script Convert-obj-js
# Enrico Navarro
# convert_obj_three.py by mrdoob -
# https://github.com/mrdoob/three.js/blob/master/utils/converters/obj/convert_obj_three.py
# V1.0

import os

def main():
    ''' Função principal do código, le o comando, separa o caminho e chama
        outras funções. '''
    #Le o comando / Exemplo, obj/*.obj
    s = raw_input("Digite o comando: ")

    #Separa o Caminho
    i = s.index('*')
    caminho = s[:i]

    #Chamada de Funções
    objList = readObj(caminho)
    convertJs(objList,caminho)

    raw_input("\nEnd of Code")

def readObj(caminho):
    ''' Função responsavel por listar todos os arquivos dentro de um diretório'''
    listaObj = os.listdir(caminho)
    return listaObj

def convertJs(objList,caminho):
    ''' Função responsavel por executar a interação dentro da lista do diretório,
        Caso o elemento da lista for .obj converte para .js. '''
    for i in objList:
        indice = i.index(".")
        if(i[indice:] != '.obj'):
            print "\nArquivo Invalido"
        else:
            comando = "convert_obj_three.py -i " + caminho + i + " -o " + "json/" + i[:indice] + ".js" 
            os.system(comando)
            print "\nConversao realizada com sucesso."
    
main()
