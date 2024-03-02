import { Pipe, PipeTransform } from "@angular/core";

@Pipe({
    name: 'replaceEmpty'
})

export class ReplaceEmpty implements PipeTransform{

    transform(value: any) : any{
        if(!!value){
            return value;
        }else{
            return "-";
        }
    }

}